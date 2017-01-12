<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImportedMedicalRecords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('imported_medical_records', function (Blueprint $table) {
            $table->increments('id');

            $table->string('medical_record_type')->required();
            $table->unsignedInteger('medical_record_id')->required();
            $table->unsignedInteger('billing_provider_id')->nullable();
            $table->unsignedInteger('location_id')->nullable();
            $table->unsignedInteger('practice_id')->nullable();

            $table->foreign('billing_provider_id')
                ->references('id')
                ->on('users')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table->foreign('location_id')
                ->references('id')
                ->on('locations')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table->foreign('practice_id')
                ->references('id')
                ->on('practices')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('imported_medical_records');
    }
}
