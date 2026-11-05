<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApiLogsTable extends Migration
{
    /**
     * Append-only log of every outbound HTTP call (Areeba, core banking).
     * Rows are never updated or deleted by the application.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('payment_id')->nullable()->index();
            $table->string('service_name', 50)->index();    // areeba | core_banking
            $table->string('action', 50)->nullable();       // authenticate, checkout_session, get_transaction, inquiry, payment_push
            $table->string('method', 10);
            $table->string('endpoint', 500);
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->unsignedInteger('execution_time_ms')->nullable();
            $table->json('request_payload')->nullable();    // credentials redacted
            $table->longText('response_payload')->nullable();
            $table->string('error', 500)->nullable();
            $table->timestamp('created_at')->nullable()->index();
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('api_logs');
    }
}
