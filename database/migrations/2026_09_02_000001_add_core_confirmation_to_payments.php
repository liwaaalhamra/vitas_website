<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCoreConfirmationToPayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            // Posting the paid installment back to the Vitas core (web-abacus),
            // same as the legacy ZainCash flow did via CustomerPaymentDetails.
            $table->timestamp('confirmed_at')->nullable()->after('paid_at');
            $table->unsignedTinyInteger('confirm_attempts')->default(0)->after('confirmed_at');
            $table->string('confirm_error')->nullable()->after('confirm_attempts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['confirmed_at', 'confirm_attempts', 'confirm_error']);
        });
    }
}
