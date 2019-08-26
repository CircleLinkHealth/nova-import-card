<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNurseInvoiceDailyDisputesTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('nurse_invoice_daily_disputes');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('nurse_invoice_daily_disputes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('invoice_id');
            $table->string('suggested_formatted_time')->nullable();
            $table->string('disputed_formatted_time')->nullable();
            $table->date('disputed_day')->nullable();
            $table->enum('status', ['approved', 'rejected', 'pending'])->nullable();
            $table->boolean('invalidated')->default(false);
            $table->timestamps();

            $table->foreign('invoice_id')
                ->references('id')
                ->on('nurse_invoices')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }
}
