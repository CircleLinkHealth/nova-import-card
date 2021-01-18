<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientForcedChargeableServicesTable extends Migration
{
    const TABLE_NAME = 'patient_forced_chargeable_services';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable(self::TABLE_NAME)){
            Schema::create(self::TABLE_NAME, function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('patient_user_id');
                $table->unsignedInteger('chargeable_services');
                $table->date('chargeable_month');

                $table->boolean('is_forced');
                $table->timestamps();

                $table->foreign('patient_user_id')
                      ->references('id')
                      ->on('users')
                      ->cascadeOnDelete();

                $table->foreign('chargeable_services')
                      ->references('id')
                      ->on('chargeable_services')
                      ->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}