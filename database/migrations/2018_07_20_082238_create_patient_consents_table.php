<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePatientConsentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_consents', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('chargeable_service_id');
            $table->unsignedInteger('user_id');
            $table->date('consented_at');
            $table->timestamps();

            $table->foreign('chargeable_service_id')
                  ->references('id')
                  ->on('chargeable_services')
                  ->onUpdate('cascade');

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
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
        Schema::dropIfExists('consents');
    }
}
