<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('reference')->unique();          // our merchantTransactionId
            $table->string('channel')->index();             // app | web
            $table->string('gateway')->default('areeba');
            $table->string('purpose')->default('loan_installment');

            $table->string('status')->default('pending')->index();
            $table->string('status_reason')->nullable();

            $table->string('gateway_uuid')->nullable()->index();
            $table->string('gateway_purchase_id')->nullable();
            $table->string('payment_method')->nullable();

            $table->unsignedBigInteger('app_user_id')->nullable()->index();
            $table->string('client_id')->nullable();        // ClientID / employee_id from core
            $table->string('loan_number')->nullable()->index();
            $table->string('branch_code')->nullable();
            $table->string('installment_number')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('customer_name')->nullable();

            $table->decimal('amount', 14, 2);               // amount actually sent to Areeba
            $table->string('currency', 3);
            $table->decimal('amount_usd', 14, 2)->nullable();
            $table->decimal('amount_iqd', 16, 2)->nullable();
            $table->decimal('exchange_rate', 14, 4)->nullable();

            $table->text('redirect_url')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->json('meta')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
