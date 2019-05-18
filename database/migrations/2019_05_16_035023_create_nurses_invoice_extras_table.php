<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNursesInvoiceExtrasTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('nurses_invoice_extras');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('nurses_invoice_extras', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->date('date')->nullable();
            $table->string('unit')->nullable();
            $table->integer('value')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('nurse_info');
        });
    }
}
