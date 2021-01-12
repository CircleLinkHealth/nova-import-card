<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDisputesTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('disputes');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('disputes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('disputable_id');
            $table->string('disputable_type');
            $table->unsignedInteger('invoice_id');
            $table->text('reason');
            $table->dateTime('resolved_at')->nullable();
            $table->unsignedInteger('user_id');
            $table->text('resolution_note')->nullable();
            $table->timestamps();

            $table->foreign('invoice_id')
                ->references('id')
                ->on('nurse_invoices')
                ->onUpdate('cascade');
        });
    }
}
