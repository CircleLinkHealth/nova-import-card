<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNurseInvoices extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        //  Schema::dropIfExists('nurse_invoices');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('nurse_invoices', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('nurse_info_id');
            $table->date('month_year');
            $table->json('invoice_data');
            $table->dateTime('sent_to_accountant');
            $table->timestamps();

            $table->foreign('nurse_info_id')
                ->references('id')
                ->on('nurse_info')
                ->onUpdate('cascade');
        });
    }
}
