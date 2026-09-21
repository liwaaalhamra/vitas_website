<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('payment_id')->index();
            $table->string('type');                 // init_request, init_response, redirect,
                                                    // callback, status_request, status_response,
                                                    // points_awarded, error
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->text('message')->nullable();
            $table->json('context')->nullable();    // secrets / PAN redacted before write
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_events');
    }
}
