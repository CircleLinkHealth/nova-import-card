<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNurseInvoices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nurse_invoices', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('nurse_info_id');
            $table->date('month_year');
            $table->dateTime('sent_to_accountant');
            $table->json('invoice_data');
            $table->timestamps();

            $table->foreign('nurse_info_id')
                ->references('id')
                ->on('nurseInfo')
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
        Schema::dropIfExists('nurse_invoices');
    }
}
