<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCpmMedicationGroupsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cpm_medication_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('care_item_id')->unsigned()->nullable()->index('cpm_medication_groups_care_item_id_foreign');
            $table->string('name')->unique();
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
        Schema::drop('cpm_medication_groups');
    }
}
