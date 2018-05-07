<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIcd9ToCentralMapsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('snomed_to_cpm_icd_maps', function (Blueprint $table) {
            $table->string('icd_9_code');
            $table->string('icd_9_name');
            $table->double('icd_9_avg_usage');
            $table->boolean('icd_9_is_nec');

            $table->unsignedInteger('cpm_problem_id')
                ->nullable();

            $table->foreign('cpm_problem_id')
                ->references('id')
                ->on('cpm_problems')
                ->onDelete('cascade')
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
        Schema::table('snomed_to_cpm_icd_maps', function (Blueprint $table) {
            //
        });
    }
}
