<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNurseInvoiceExtrasTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('nurse_invoice_extras');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('nurse_invoice_extras', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->date('date')->nullable();
            $table->string('unit')->nullable();
            $table->integer('value')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade');
        });
    }
}
