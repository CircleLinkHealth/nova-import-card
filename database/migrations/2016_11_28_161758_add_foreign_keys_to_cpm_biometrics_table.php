<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCpmBiometricsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cpm_biometrics', function (Blueprint $table) {
            $table->foreign('care_item_id')->references('id')->on('care_items')->onUpdate('CASCADE')->onDelete('RESTRICT');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cpm_biometrics', function (Blueprint $table) {
            $table->dropForeign('cpm_biometrics_care_item_id_foreign');
        });
    }
}
