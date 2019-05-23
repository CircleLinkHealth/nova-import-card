<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceDisputes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_disputes', function (Blueprint $table) {
            $table->increments('id');
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoice_disputes');
    }
}
