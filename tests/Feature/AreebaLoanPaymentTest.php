<?php

namespace Tests\Feature;

use App\Services\Areeba\AreebaException;
use App\Services\Areeba\AreebaPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class AreebaLoanPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // These two tables exist in the real DB but are not tracked by migrations.
        if (!Schema::hasTable('app_users')) {
            Schema::create('app_users', function (Blueprint $t) {
                $t->increments('id');
                $t->integer('employee_id')->nullable();
                $t->integer('total_points')->nullable();
                $t->string('customer_name')->nullable();
                $t->timestamps();
            });
        }

        if (!Schema::hasTable('app_users_points')) {
            Schema::create('app_users_points', function (Blueprint $t) {
                $t->increments('id');
                $t->integer('user_id')->nullable();
                $t->string('meta_name')->nullable();
                $t->string('meta_value')->nullable();
                $t->integer('points')->nullable();
                $t->text('description')->nullable();
                $t->timestamps();
            });
        }

        // Tables the website middleware reads on every web request.
        if (!Schema::hasTable('seo_meta_tags')) {
            Schema::create('seo_meta_tags', function (Blueprint $t) {
                $t->increments('id');
                $t->string('title')->nullable();
                $t->string('description')->nullable();
                $t->string('keywords')->nullable();
            });
            DB::table('seo_meta_tags')->insert(['title' => 'Vitas', 'description' => 'x', 'keywords' => 'x']);
        }

        if (!Schema::hasTable('contact_infos')) {
            Schema::create('contact_infos', function (Blueprint $t) {
                $t->increments('id');
                $t->string('slug')->nullable();
                $t->string('value_en')->nullable();
                $t->string('value_ar')->nullable();
            });
        }
    }

    private function service(): AreebaPaymentService
    {
        return app(AreebaPaymentService::class);
    }

    private function inquiry(array $overrides = []): array
    {
        return array_merge([
            'ClientID'           => 12345,
            'PhoneNumber'        => '9647800000000',
            'PaymentNumber'      => 3,
            'TotalPaymentDueUSD' => 100.00,
            'TotalPaymentDueIQD' => 145000,
            'BranchCode'         => 'B01',
            'LoanNumber'         => 900012345,
            'ExchangeRate'       => 1450,
            'LoanCurrency'       => 'USD',
            'CustomerName'       => 'John Doe',
        ], $overrides);
    }

    private function fakeGateway(?array $debit = null, ?array $status = null): void
    {
        Http::fake([
            '*/transaction/*/debit' => Http::response($debit ?? [
                'success'       => true,
                'uuid'          => 'uuid-abc',
                'purchaseId'    => '20260901-uuid-abc',
                'returnType'    => 'REDIRECT',
                'redirectUrl'   => 'https://gateway.test/redirect/uuid-abc/token',
                'paymentMethod' => 'Creditcard',
            ], 200),

            '*/status/*/getByMerchantTransactionId/*' => Http::response($status ?? [
                'transactionStatus' => 'SUCCESS',
                'success'           => true,
                'uuid'              => 'uuid-abc',
                'amount'            => '100.00',
                'currency'          => 'USD',
                'paymentMethod'     => 'Creditcard',
            ], 200),

            '*CustomerDataInquiry*'    => Http::response($this->inquiry(), 200),
            '*CustomerPaymentDetails*' => Http::response(['StatusCode' => 0, 'ActionStatus' => 'OK'], 200),
        ]);
    }

    public function test_start_loan_payment_opens_transaction_and_stores_redirect_url(): void
    {
        $this->fakeGateway();

        $payment = $this->service()->startLoanPayment($this->inquiry(), 'app', 77);

        $this->assertSame('redirected', $payment->status);
        $this->assertSame('https://gateway.test/redirect/uuid-abc/token', $payment->redirect_url);
        $this->assertSame('uuid-abc', $payment->gateway_uuid);
        $this->assertSame('100.00', (string) $payment->amount);
        $this->assertSame('USD', $payment->currency);
        $this->assertDatabaseHas('payment_events', ['payment_id' => $payment->id, 'type' => 'init_request']);
        $this->assertDatabaseHas('payment_events', ['payment_id' => $payment->id, 'type' => 'init_response']);
    }

    public function test_charge_currency_follows_loan_currency(): void
    {
        $this->fakeGateway();

        $payment = $this->service()->startLoanPayment($this->inquiry(['LoanCurrency' => 'IQD']), 'app', 1);

        $this->assertSame('IQD', $payment->currency);
        $this->assertSame('145000.00', (string) $payment->amount);
    }

    public function test_init_failure_marks_payment_error_and_throws(): void
    {
        $this->fakeGateway(debit: ['success' => false, 'errors' => [['message' => 'Invalid merchant']]]);

        try {
            $this->service()->startLoanPayment($this->inquiry(), 'web', null);
            $this->fail('Expected AreebaException');
        } catch (AreebaException $e) {
            $this->assertStringContainsString('Invalid merchant', $e->getMessage());
        }

        $this->assertDatabaseHas('payments', ['status' => 'error']);
    }

    public function test_successful_status_marks_paid_and_awards_points_once(): void
    {
        $this->fakeGateway();
        DB::table('app_users')->insert(['employee_id' => 12345, 'total_points' => 10]);

        $payment = $this->service()->startLoanPayment($this->inquiry(), 'app', 5);

        $this->service()->syncFromGateway($payment->fresh());
        $this->service()->syncFromGateway($payment->fresh()); // duplicate callback

        $payment->refresh();
        $this->assertSame('paid', $payment->status);
        $this->assertNotNull($payment->paid_at);
        $this->assertNotNull($payment->confirmed_at, 'installment should be confirmed to the core');
        $this->assertSame(1, DB::table('app_users_points')->where('meta_name', 'areeba')->count());
        $this->assertSame(160, (int) DB::table('app_users')->where('employee_id', 12345)->value('total_points'));
    }

    public function test_paid_installment_is_posted_back_to_the_vitas_core(): void
    {
        $this->fakeGateway();

        $payment = $this->service()->startLoanPayment($this->inquiry(), 'app', 5);
        $this->service()->syncFromGateway($payment->fresh());

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'CustomerPaymentDetails')
                && $request['LoanNumber'] == 900012345
                && $request['PaymentMethod'] === 'Areeba';
        });
        $this->assertSame(1, (int) $payment->fresh()->confirm_attempts);
        $this->assertNotNull($payment->fresh()->confirmed_at);
    }

    public function test_core_confirmation_failure_keeps_payment_paid_and_reconcile_retries(): void
    {
        Http::fake([
            '*/transaction/*/debit' => Http::response([
                'success' => true, 'uuid' => 'u1', 'returnType' => 'REDIRECT',
                'redirectUrl' => 'https://gateway.test/redirect/u1', 'paymentMethod' => 'Creditcard',
            ], 200),
            '*/status/*/getByMerchantTransactionId/*' => Http::response([
                'transactionStatus' => 'SUCCESS', 'success' => true, 'amount' => '100.00', 'currency' => 'USD',
            ], 200),
            '*CustomerDataInquiry*' => Http::response($this->inquiry(), 200),
            '*CustomerPaymentDetails*' => Http::sequence()
                ->push(['StatusCode' => 1, 'ActionStatus' => 'core down'], 200)   // first attempt fails
                ->push(['StatusCode' => 0], 200),                                  // reconcile succeeds
        ]);

        $payment = $this->service()->startLoanPayment($this->inquiry(), 'app', 5);
        $this->service()->syncFromGateway($payment->fresh());

        $payment->refresh();
        $this->assertSame('paid', $payment->status);
        $this->assertNull($payment->confirmed_at);
        $this->assertSame(1, (int) $payment->confirm_attempts);
        $this->assertNotNull($payment->confirm_error);

        $this->artisan('payments:reconcile')->assertExitCode(0);

        $this->assertNotNull($payment->fresh()->confirmed_at);
    }

    public function test_core_confirmation_can_be_disabled(): void
    {
        config(['services.vitas_core.confirm_enabled' => false]);
        $this->fakeGateway();

        $payment = $this->service()->startLoanPayment($this->inquiry(), 'app', 5);
        $this->service()->syncFromGateway($payment->fresh());

        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertNull($payment->fresh()->confirmed_at);
        $this->assertSame(0, (int) $payment->fresh()->confirm_attempts);
        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'CustomerPaymentDetails'));
    }

    public function test_web_channel_does_not_award_points(): void
    {
        $this->fakeGateway();

        $payment = $this->service()->startLoanPayment($this->inquiry(), 'web', null);
        $this->service()->syncFromGateway($payment->fresh());

        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame(0, DB::table('app_users_points')->count());
    }

    public function test_error_status_marks_failed_without_points(): void
    {
        $this->fakeGateway(status: ['transactionStatus' => 'ERROR', 'success' => false, 'errors' => [['message' => 'Declined by issuer']]]);

        $payment = $this->service()->startLoanPayment($this->inquiry(), 'app', 5);
        $this->service()->syncFromGateway($payment->fresh());

        $this->assertSame('failed', $payment->fresh()->status);
        $this->assertSame(0, DB::table('app_users_points')->count());
    }

    public function test_amount_mismatch_is_flagged_and_not_paid(): void
    {
        $this->fakeGateway(status: [
            'transactionStatus' => 'SUCCESS',
            'success'           => true,
            'amount'            => '5.00',
            'currency'          => 'USD',
        ]);

        $payment = $this->service()->startLoanPayment($this->inquiry(), 'app', 5);
        $this->service()->syncFromGateway($payment->fresh());

        $this->assertSame('mismatch', $payment->fresh()->status);
        $this->assertSame(0, DB::table('app_users_points')->count());
    }

    public function test_website_form_page_renders(): void
    {
        $this->get('/en/pay/loan')
            ->assertOk()
            ->assertSee('loan_number', false);
    }

    public function test_website_route_redirects_to_gateway(): void
    {
        $this->fakeGateway();

        $response = $this->post('/en/pay/loan', ['loan_number' => '900012345']);

        $response->assertRedirect('https://gateway.test/redirect/uuid-abc/token');
        $this->assertDatabaseHas('payments', ['channel' => 'web', 'status' => 'redirected', 'loan_number' => '900012345']);
    }

    public function test_website_route_shows_error_when_no_installment(): void
    {
        Http::fake(['*CustomerDataInquiry*' => Http::response(['LoanNumber' => 'LN-1', 'TotalPaymentDueUSD' => 0, 'TotalPaymentDueIQD' => 0], 200)]);

        $response = $this->from('/en/pay/loan')->post('/en/pay/loan', ['loan_number' => 'LN-1']);

        $response->assertRedirect('/en/pay/loan');
        $response->assertSessionHas('pay_error');
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_reconcile_command_settles_stale_payments_only(): void
    {
        $this->fakeGateway();

        $fresh = $this->service()->startLoanPayment($this->inquiry(), 'app', 1);

        $stale = $this->service()->startLoanPayment($this->inquiry(), 'app', 2);
        $stale->forceFill(['created_at' => now()->subHour()])->save();

        $this->artisan('payments:reconcile')->assertExitCode(0);

        $this->assertSame('paid', $stale->fresh()->status);
        $this->assertSame('redirected', $fresh->fresh()->status);
    }
}
