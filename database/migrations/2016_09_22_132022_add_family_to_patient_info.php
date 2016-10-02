<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddFamilyToPatientInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_info', function (Blueprint $table) {

            if (Schema::hasColumn('patient_info', 'family_id')) {
                return;
            }

            $table->unsignedInteger('family_id')->nullable();

            $table->foreign('family_id')
                ->references('id')
                ->on('families')
                ->onUpdate('cascade')
                ->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_info', function (Blueprint $table) {
            //
        });
    }
}
