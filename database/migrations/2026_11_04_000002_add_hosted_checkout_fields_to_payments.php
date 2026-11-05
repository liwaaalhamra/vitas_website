<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddHostedCheckoutFieldsToPayments extends Migration
{
    /**
     * Areeba hosted checkout (payments-api.areeba.com) + core banking push tracking.
     *
     * Status lifecycle: initiated -> session_created -> paid | failed | cancelled
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('areeba_transaction_id')->nullable()->index()->after('gateway_purchase_id');
            $table->json('areeba_response_json')->nullable()->after('areeba_transaction_id');
            $table->string('locale', 2)->nullable()->after('channel');
            $table->string('loan_product')->nullable()->after('branch_code');
            $table->string('branch_name')->nullable()->after('branch_code');
            $table->date('due_date')->nullable()->after('loan_product');

            $table->string('banking_push_status', 20)->nullable()->index()->after('paid_at'); // pending | success | failed
            $table->string('banking_status_code', 50)->nullable()->after('banking_push_status');
            $table->string('banking_action_status', 500)->nullable()->after('banking_status_code');
            $table->json('banking_response_json')->nullable()->after('banking_action_status');
            $table->unsignedSmallInteger('banking_push_attempts')->default(0)->after('banking_response_json');
            $table->timestamp('banking_pushed_at')->nullable()->after('banking_push_attempts');
            $table->timestamp('banking_last_attempt_at')->nullable()->after('banking_pushed_at');
        });

        // Map the statuses of the first (IXOPAY) integration onto the new lifecycle.
        $map = [
            'pending'    => 'initiated',
            'redirected' => 'session_created',
            'declined'   => 'failed',
            'error'      => 'failed',
            'mismatch'   => 'failed',
            'expired'    => 'cancelled',
        ];
        foreach ($map as $old => $new) {
            DB::table('payments')->where('status', $old)->update(['status' => $new]);
        }

        DB::table('payments')->where('status', 'paid')->whereNotNull('confirmed_at')
            ->update(['banking_push_status' => 'success', 'banking_pushed_at' => DB::raw('confirmed_at')]);
        DB::table('payments')->where('status', 'paid')->whereNull('confirmed_at')
            ->update(['banking_push_status' => 'failed', 'banking_action_status' => DB::raw('confirm_error')]);
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'areeba_transaction_id', 'areeba_response_json', 'locale', 'loan_product', 'branch_name', 'due_date',
                'banking_push_status', 'banking_status_code', 'banking_action_status', 'banking_response_json',
                'banking_push_attempts', 'banking_pushed_at', 'banking_last_attempt_at',
            ]);
        });
    }
}
