<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultInstructionsForCareplanTemplate extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('care_plan_templates_cpm_lifestyles', function (Blueprint $table) {
            $table->text('default_instruction')
                ->nullable()
                ->default(null)
                ->after('ui_sort');
        });

        Schema::table('care_plan_templates_cpm_symptoms', function (Blueprint $table) {
            $table->text('default_instruction')
                ->nullable()
                ->default(null)
                ->after('ui_sort');
        });

        Schema::table('care_plan_templates_cpm_medication_groups', function (Blueprint $table) {
            $table->text('default_instruction')
                ->nullable()
                ->default(null)
                ->after('ui_sort');
        });

        Schema::table('care_plan_templates_cpm_problems', function (Blueprint $table) {
            $table->text('default_instruction')
                ->nullable()
                ->default(null)
                ->after('ui_sort');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('care_plan_templates_cpm_lifestyles', function(Blueprint $table)
        {
            $table->dropColumn('default_instruction');
        });

        Schema::table('care_plan_templates_cpm_symptoms', function(Blueprint $table)
        {
            $table->dropColumn('default_instruction');
        });

        Schema::table('care_plan_templates_cpm_medication_groups', function(Blueprint $table)
        {
            $table->dropColumn('default_instruction');
        });

        Schema::table('care_plan_templates_cpm_problems', function(Blueprint $table)
        {
            $table->dropColumn('default_instruction');
        });
    }

}
