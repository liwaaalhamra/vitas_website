<?php

namespace Tests\Feature;

use App\Models\ApiLog;
use App\Models\Payment;
use App\Services\Areeba\AreebaPaymentService;
use App\Support\Mask;
use App\Support\Secret;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AreebaLoanPaymentTest extends TestCase
{
    use RefreshDatabase;

    const AREEBA = 'https://areeba.test/prod/api';
    const CORE = 'https://core.test/api/VitasPayments';

    /** @var array|callable|null GetTransaction response for the current test */
    private $transaction;
    private array $pushResponse = ['StatusCode' => 0, 'ActionStatus' => 'Success'];
    private array $inquiryOverrides = [];
    private array $sessionResponse = ['data' => ['checkoutUrl' => 'https://checkout.areeba.test/pay/abc']];

    protected function setUp(): void
    {
        parent::setUp();

        // These tables exist in the real DB but are not tracked by migrations.
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
            foreach (['call-center-number', 'facebook', 'instagram', 'our-address', 'working-hours', 'youtube'] as $slug) {
                DB::table('contact_infos')->insert(['slug' => $slug, 'value_en' => 'x', 'value_ar' => 'x']);
            }
        }

        $this->transaction = ['transactionId' => 'TX-1', 'status' => 'Approved', 'chargeAmount' => 150000, 'currency' => 'IQD'];
        $this->fakeApis();
    }

    // ------------------------------------------------------------------ fixtures

    private function inquiryResponse(): array
    {
        return array_merge([
            'StatusCode'         => 0,
            'ActionStatus'       => 'Success',
            'LoanNumber'         => '10023456',
            'CustomerName'       => 'Mohammed Ali Hassan',
            'PhoneNumber'        => '9647701234567',
            'BranchName'         => 'Baghdad - Karrada',
            'LoanProduct'        => 'Micro business loan',
            'DueDate'            => '2026-11-15T00:00:00',
            'TotalPaymentDueIQD' => 150000,
            'ClientID'           => 12345,
            'PaymentNumber'      => 3,
        ], $this->inquiryOverrides);
    }

    private function fakeApis(): void
    {
        Http::fake(function (HttpRequest $request) {
            $url = $request->url();

            if (str_starts_with($url, self::AREEBA.'/Authentication')) {
                return Http::response(['access_token' => 'bearer-123', 'expires_in' => 3600]);
            }
            if (str_starts_with($url, self::AREEBA.'/tokens/request')) {
                return Http::response($this->sessionResponse);
            }
            if (str_starts_with($url, self::AREEBA.'/v1/payments/GetTransaction/')) {
                $body = is_callable($this->transaction) ? ($this->transaction)($request) : $this->transaction;

                return Http::response($body);
            }
            if ($url === self::CORE.'/CustomerDataInquiryByLoanNumber') {
                return Http::response($this->inquiryResponse());
            }
            if ($url === self::CORE.'/CustomerPaymentDetails') {
                return Http::response($this->pushResponse);
            }

            return Http::response('unexpected '.$url, 500);
        });
    }

    private function service(): AreebaPaymentService
    {
        return app(AreebaPaymentService::class);
    }

    /** Inquiry + checkout through the website, returns the created payment. */
    private function checkout(string $locale = 'ar'): Payment
    {
        $this->postJson("/{$locale}/payment/inquiry", ['loan_number' => '10023456'])->assertOk();
        $this->postJson("/{$locale}/payment/checkout")->assertOk();

        return Payment::latest('id')->firstOrFail();
    }

    private function pushCalls(): int
    {
        return count(Http::recorded(fn ($request) => str_ends_with($request->url(), '/CustomerPaymentDetails')));
    }

    private function sign(string $body): string
    {
        return hash_hmac('sha256', $body, 'whsecret');
    }

    // ------------------------------------------------------------------ inquiry

    public function test_payment_page_renders(): void
    {
        $this->get('/en/payment')->assertOk()->assertSee('Loan number', false)->assertSee('Proceed to Secure Payment', false);
    }

    public function test_legacy_pay_loan_url_redirects_to_payment_page(): void
    {
        $this->get('/en/pay/loan')->assertRedirect('/en/payment');
    }

    public function test_inquiry_returns_masked_details_only(): void
    {
        $response = $this->postJson('/en/payment/inquiry', ['loan_number' => '10023456'])->assertOk();

        $response->assertJson([
            'loan_number'  => '10023456',
            'name'         => 'Mo****** A** H*****',
            'phone'        => '*********4567',
            'branch'       => 'Baghdad - Karrada',
            'loan_product' => 'Micro business loan',
            'due_date'     => '2026-11-15',
            'amount'       => '150,000',
            'currency'     => 'IQD',
        ]);
        $this->assertStringNotContainsString('Mohammed', $response->getContent());
        $this->assertStringNotContainsString('9647701234567', $response->getContent());

        Http::assertSent(fn ($request) => $request->url() === self::CORE.'/CustomerDataInquiryByLoanNumber'
            && $request['LoanNumber'] === '10023456' && $request['username'] === 'inq' && $request['password'] === 'inqpass');
    }

    public function test_inquiry_for_unknown_loan_returns_clear_message(): void
    {
        $this->inquiryOverrides = ['StatusCode' => 9, 'ActionStatus' => 'Loan not found'];

        $this->postJson('/en/payment/inquiry', ['loan_number' => '999'])
            ->assertStatus(404)
            ->assertJsonFragment(['message' => 'No amount due was found for this loan number. Please check the number and try again.']);
    }

    public function test_inquiry_with_nothing_due_is_not_payable(): void
    {
        $this->inquiryOverrides = ['TotalPaymentDueIQD' => 0];

        $this->postJson('/en/payment/inquiry', ['loan_number' => '10023456'])->assertStatus(404);
    }

    public function test_inquiry_is_rate_limited_per_ip(): void
    {
        for ($i = 0; $i < 15; $i++) {
            $this->postJson('/en/payment/inquiry', ['loan_number' => '10023456'])->assertOk();
        }

        $this->postJson('/en/payment/inquiry', ['loan_number' => '10023456'])->assertStatus(429);
    }

    // ------------------------------------------------------------------ checkout

    public function test_checkout_without_a_recent_inquiry_is_rejected(): void
    {
        $this->postJson('/en/payment/checkout')->assertStatus(422);
        $this->assertSame(0, Payment::count());
    }

    public function test_checkout_creates_session_and_returns_checkout_url(): void
    {
        $this->postJson('/ar/payment/inquiry', ['loan_number' => '10023456'])->assertOk();
        $this->postJson('/ar/payment/checkout')
            ->assertOk()
            ->assertJson(['redirect_url' => 'https://checkout.areeba.test/pay/abc']);

        $payment = Payment::firstOrFail();
        $this->assertSame('session_created', $payment->status);
        $this->assertSame('150000.00', $payment->amount);
        $this->assertSame('IQD', $payment->currency);
        $this->assertSame('Mohammed Ali Hassan', $payment->customer_name);
        $this->assertSame('ar', $payment->locale);
        $this->assertArrayHasKey('session', $payment->areeba_response_json);

        $request = collect(Http::recorded())->map(fn ($pair) => $pair[0])
            ->first(fn ($request) => str_starts_with($request->url(), self::AREEBA.'/tokens/request'));
        $this->assertNotNull($request);
        $params = collect($request['parameters'])->pluck('value', 'key');

        $this->assertTrue($request->hasHeader('Authorization', 'Bearer bearer-123'), 'bearer token');
        $this->assertTrue($request->hasHeader('Ocp-Apim-Subscription-Key', 'subkey'), 'subscription key');
        $this->assertSame(93000533, $params['productId']);
        $this->assertEquals(150000, $params['chargeAmount']);
        $this->assertSame('True', $params['isTaxIncluded']);
        $this->assertSame($payment->reference, $params['pt']);
        $this->assertSame('IQD', $params['currency']);
        $this->assertSame('ar-iq', $params['lang-country']);
        $this->assertSame(4, $request['tokenTypeId']);
        $this->assertSame('Vitas PGW', $request['u']);
        $this->assertTrue($request['p'] === 'apipass', 'gateway password');

        // the held inquiry is single use
        $this->postJson('/ar/payment/checkout')->assertStatus(422);
    }

    public function test_checkout_failure_marks_payment_failed(): void
    {
        $this->sessionResponse = ['message' => 'Invalid product'];

        $this->postJson('/en/payment/inquiry', ['loan_number' => '10023456'])->assertOk();
        $this->postJson('/en/payment/checkout')->assertStatus(502);

        $payment = Payment::firstOrFail();
        $this->assertSame('failed', $payment->status);
        $this->assertStringContainsString('Invalid product', $payment->status_reason);
    }

    // ------------------------------------------------------------------ return / verification

    public function test_successful_return_marks_paid_pushes_to_core_and_shows_receipt(): void
    {
        $payment = $this->checkout('en');

        $response = $this->get('/payment/return?ref='.$payment->reference.'&transactionId=TX-1');
        $payment->refresh();

        $response->assertRedirect(URL::signedRoute('payment.result', ['locale' => 'en', 'reference' => $payment->reference]));
        $this->assertSame('paid', $payment->status);
        $this->assertSame('TX-1', $payment->areeba_transaction_id);
        $this->assertArrayHasKey('transaction', $payment->areeba_response_json);
        $this->assertSame('success', $payment->banking_push_status);
        $this->assertSame('0', $payment->banking_status_code);
        $this->assertSame('Success', $payment->banking_action_status);
        $this->assertSame(1, $payment->banking_push_attempts);

        Http::assertSent(fn ($request) => $request->url() === self::CORE.'/CustomerPaymentDetails'
            && $request['LoanNumber'] === '10023456'
            && $request['DueAmount'] === '150000.00'
            && $request['PaidAmount'] === '150000.00'
            && $request['TransxsID'] === 'TX-1'
            && $request['SourceCurrencyID'] === 3
            && $request['UserId'] === ''
            && $request['username'] === 'pay'
            && $request['password'] === 'paypass');

        $result = $this->get($response->headers->get('Location'))->assertOk()->assertSee('Payment successful');
        preg_match('/href="([^"]+payment\/receipt[^"]+)"/', $result->getContent(), $m);
        $this->get(html_entity_decode($m[1]))->assertOk()
            ->assertSee('Official payment receipt')
            ->assertSee('Mo****** A** H*****')
            ->assertSee('150,000 IQD')
            ->assertDontSee('Mohammed');
    }

    public function test_result_and_receipt_require_a_signed_url(): void
    {
        $payment = $this->checkout('en');
        $this->get('/payment/return?ref='.$payment->reference.'&transactionId=TX-1');

        $this->get('/en/payment/result/'.$payment->reference)->assertStatus(403);
        $this->get('/en/payment/receipt/'.$payment->reference)->assertStatus(403);
    }

    public function test_declined_transaction_is_failed_with_retry_option(): void
    {
        $this->transaction = ['transactionId' => 'TX-1', 'status' => 'Declined', 'message' => 'Insufficient funds'];
        $payment = $this->checkout('en');

        $response = $this->get('/payment/return?ref='.$payment->reference.'&transactionId=TX-1');
        $payment->refresh();

        $this->assertSame('failed', $payment->status);
        $this->assertNull($payment->banking_push_status);
        $this->assertSame(0, $this->pushCalls());
        $this->get($response->headers->get('Location'))->assertOk()->assertSee('Payment failed')->assertSee('Try again');
    }

    public function test_cancel_return_without_transaction_marks_cancelled_but_a_verified_payment_still_wins(): void
    {
        $payment = $this->checkout('en');

        $this->get('/payment/return?ref='.$payment->reference.'&status=cancelled');
        $this->assertSame('cancelled', $payment->refresh()->status);

        // The customer actually paid after all: the webhook brings the transaction
        $body = json_encode(['pt' => $payment->reference, 'transactionId' => 'TX-1']);
        $this->call('POST', '/payment/webhook', [], [], [], ['HTTP_X_SIGNATURE' => $this->sign($body), 'CONTENT_TYPE' => 'application/json'], $body)->assertOk();

        $this->assertSame('paid', $payment->refresh()->status);
        $this->assertSame('success', $payment->banking_push_status);
    }

    public function test_amount_mismatch_is_not_paid_and_not_pushed(): void
    {
        $this->transaction = ['transactionId' => 'TX-1', 'status' => 'Approved', 'chargeAmount' => 1000, 'currency' => 'IQD'];
        $payment = $this->checkout();

        $this->get('/payment/return?ref='.$payment->reference.'&transactionId=TX-1');
        $payment->refresh();

        $this->assertSame('failed', $payment->status);
        $this->assertStringContainsString('amount mismatch', $payment->status_reason);
        $this->assertSame(0, $this->pushCalls());
    }

    public function test_transaction_of_another_order_cannot_settle_this_payment(): void
    {
        $this->transaction = ['transactionId' => 'TX-OTHER', 'status' => 'Approved', 'chargeAmount' => 150000, 'pt' => 'ORD-SOMEONE-ELSE'];
        $payment = $this->checkout();

        $this->get('/payment/return?ref='.$payment->reference.'&transactionId=TX-OTHER');

        $this->assertSame('session_created', $payment->refresh()->status);
        $this->assertSame(0, $this->pushCalls());
    }

    public function test_concurrent_confirmations_push_to_core_only_once(): void
    {
        $payment = $this->checkout();

        $this->get('/payment/return?ref='.$payment->reference.'&transactionId=TX-1');
        $body = json_encode(['pt' => $payment->reference, 'transactionId' => 'TX-1']);
        $this->call('POST', '/payment/webhook', [], [], [], ['HTTP_X_SIGNATURE' => $this->sign($body), 'CONTENT_TYPE' => 'application/json'], $body);
        $this->service()->pushToBanking($payment->refresh());
        $this->artisan('payments:reconcile')->assertExitCode(0);

        $this->assertSame(1, $this->pushCalls());
        $this->assertSame(1, $payment->refresh()->banking_push_attempts);
    }

    // ------------------------------------------------------------------ webhook

    public function test_webhook_with_invalid_signature_is_rejected(): void
    {
        $payment = $this->checkout();
        $body = json_encode(['pt' => $payment->reference, 'transactionId' => 'TX-1']);

        $this->call('POST', '/payment/webhook', [], [], [], ['HTTP_X_SIGNATURE' => 'forged', 'CONTENT_TYPE' => 'application/json'], $body)->assertStatus(401);
        $this->call('POST', '/payment/webhook', [], [], [], ['CONTENT_TYPE' => 'application/json'], $body)->assertStatus(401);

        $this->assertSame('session_created', $payment->refresh()->status);
    }

    public function test_signed_webhook_settles_payment_from_get_transaction(): void
    {
        $payment = $this->checkout();
        $body = json_encode(['pt' => $payment->reference, 'transactionId' => 'TX-1', 'status' => 'whatever the body says']);

        $this->call('POST', '/payment/webhook', [], [], [], ['HTTP_X_SIGNATURE' => 'sha256='.$this->sign($body), 'CONTENT_TYPE' => 'application/json'], $body)
            ->assertOk();

        $payment->refresh();
        $this->assertSame('paid', $payment->status);
        $this->assertArrayHasKey('webhook', $payment->areeba_response_json);
        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/GetTransaction/TX-1'));
    }

    // ------------------------------------------------------------------ core banking push retries

    public function test_failed_core_push_keeps_payment_paid_and_is_retried(): void
    {
        $this->pushResponse = ['StatusCode' => 5, 'ActionStatus' => 'Loan locked'];
        $payment = $this->checkout();
        $this->get('/payment/return?ref='.$payment->reference.'&transactionId=TX-1');

        $payment->refresh();
        $this->assertSame('paid', $payment->status);
        $this->assertSame('failed', $payment->banking_push_status);
        $this->assertSame('5', $payment->banking_status_code);
        $this->assertSame('Loan locked', $payment->banking_action_status);

        $this->pushResponse = ['StatusCode' => 0, 'ActionStatus' => 'Success'];
        $this->artisan('payments:reconcile')->assertExitCode(0);

        $payment->refresh();
        $this->assertSame('success', $payment->banking_push_status);
        $this->assertSame(2, $payment->banking_push_attempts);
        $this->assertNotNull($payment->banking_pushed_at);
    }

    public function test_core_push_can_be_disabled(): void
    {
        config(['services.vitas_core.push_enabled' => false]);
        $payment = $this->checkout();

        $this->get('/payment/return?ref='.$payment->reference.'&transactionId=TX-1');

        $this->assertSame('paid', $payment->refresh()->status);
        $this->assertSame('pending', $payment->banking_push_status);
        $this->assertSame(0, $this->pushCalls());
    }

    // ------------------------------------------------------------------ reconcile

    public function test_reconcile_expires_abandoned_sessions_and_verifies_known_transactions(): void
    {
        $abandoned = $this->checkout();
        $abandoned->forceFill(['created_at' => now()->subHours(30)])->save();

        $withTransaction = $this->checkout();
        $withTransaction->forceFill(['created_at' => now()->subMinutes(20), 'areeba_transaction_id' => 'TX-1'])->save();

        $fresh = $this->checkout();

        $this->artisan('payments:reconcile')->assertExitCode(0);

        $this->assertSame('cancelled', $abandoned->refresh()->status);
        $this->assertSame('paid', $withTransaction->refresh()->status);
        $this->assertSame('session_created', $fresh->refresh()->status);
    }

    // ------------------------------------------------------------------ app channel

    public function test_app_payment_awards_loyalty_points_once(): void
    {
        DB::table('app_users')->insert(['id' => 1, 'employee_id' => 12345, 'total_points' => 10]);
        $data = app(\App\Services\VitasCoreClient::class)->inquiry('10023456')['data'];

        $payment = $this->service()->startCheckout($data, 'app', 1, 'en');
        $this->get('/payment/return?ref='.$payment->reference.'&transactionId=TX-1')
            ->assertOk()->assertSee('Payment successful');
        $this->service()->verify($payment->refresh(), 'TX-1');

        $this->assertSame(1, DB::table('app_users_points')->where('meta_value', $payment->reference)->count());
        $this->assertSame(160, (int) DB::table('app_users')->where('id', 1)->value('total_points'));
    }

    // ------------------------------------------------------------------ logging, secrets, masking

    public function test_every_outbound_call_is_logged_without_credentials(): void
    {
        $payment = $this->checkout();
        $this->get('/payment/return?ref='.$payment->reference.'&transactionId=TX-1');

        $actions = ApiLog::orderBy('id')->pluck('action')->all();
        $this->assertSame(['inquiry', 'authenticate', 'checkout_session', 'get_transaction', 'payment_push'], $actions);

        $all = ApiLog::all()->map(fn ($log) => json_encode($log->request_payload).$log->response_payload)->implode(' ');
        foreach (['inqpass', 'paypass', 'apipass', 'bearer-123'] as $secret) {
            $this->assertStringNotContainsString($secret, $all);
        }

        $log = ApiLog::where('action', 'payment_push')->first();
        $this->assertSame($payment->id, $log->payment_id);
        $this->assertSame(200, $log->http_status);
        $this->assertNotNull($log->execution_time_ms);
    }

    public function test_api_logs_are_append_only(): void
    {
        $this->postJson('/en/payment/inquiry', ['loan_number' => '10023456']);
        $log = ApiLog::firstOrFail();

        $this->expectException(\LogicException::class);
        $log->update(['http_status' => 500]);
    }

    public function test_encrypted_secrets_are_decrypted_at_use(): void
    {
        $encrypted = Secret::encrypt('s3cret-pass');
        $this->assertStringStartsWith('enc:', $encrypted);
        $this->assertSame('s3cret-pass', Secret::reveal($encrypted));
        $this->assertSame('plain', Secret::reveal('plain'));

        config(['services.vitas_core.inquiry_password' => $encrypted]);
        $this->postJson('/en/payment/inquiry', ['loan_number' => '10023456'])->assertOk();

        Http::assertSent(fn ($request) => str_ends_with($request->url(), 'ByLoanNumber') && $request['password'] === 's3cret-pass');
    }

    public function test_masking(): void
    {
        $this->assertSame('Mo****** A** H*****', Mask::name('Mohammed  Ali Hassan'));
        $this->assertSame('أح** ع**', Mask::name('أحمد علي'));
        $this->assertSame('A*', Mask::name('Al'));
        $this->assertSame('*********4567', Mask::phone('+964 770 123 4567'));
        $this->assertSame('****', Mask::phone('1234'));
        $this->assertNull(Mask::name(''));
    }
}
