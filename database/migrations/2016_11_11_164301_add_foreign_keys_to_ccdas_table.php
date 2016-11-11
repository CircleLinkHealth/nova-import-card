<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCcdasTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ccdas', function (Blueprint $table) {
            $table->foreign('patient_id',
                'users_patient_id_foreign')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('RESTRICT');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ccdas', function (Blueprint $table) {
            $table->dropForeign('users_patient_id_foreign');
        });
    }

}
