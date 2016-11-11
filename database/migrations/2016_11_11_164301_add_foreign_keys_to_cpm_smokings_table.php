<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCpmSmokingsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cpm_smokings', function (Blueprint $table) {
            $table->foreign('patient_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cpm_smokings', function (Blueprint $table) {
            $table->dropForeign('cpm_smokings_patient_id_foreign');
        });
    }

}
