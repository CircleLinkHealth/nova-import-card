<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToPatientInfoTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_info', function (Blueprint $table) {
            $table->foreign('family_id')->references('id')->on('families')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('next_call_id')->references('id')->on('calls')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
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
            $table->dropForeign('patient_info_family_id_foreign');
            $table->dropForeign('patient_info_next_call_id_foreign');
            $table->dropForeign('patient_info_user_id_foreign');
        });
    }

}
