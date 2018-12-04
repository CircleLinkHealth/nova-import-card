<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToMedicationGroupsMapsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('medication_groups_maps', function (Blueprint $table) {
            $table->foreign('medication_group_id')->references('id')->on('cpm_medication_groups')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('medication_groups_maps', function (Blueprint $table) {
            $table->dropForeign('medication_groups_maps_medication_group_id_foreign');
        });
    }
}
