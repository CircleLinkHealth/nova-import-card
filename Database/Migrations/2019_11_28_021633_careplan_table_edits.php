<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CareplanTableEdits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'care_plan_templates_cpm_biometrics',
            function (Blueprint $table) {
                $table->unsignedInteger('page')->nullable()->change();
            }
        );
        
        Schema::table(
            'care_plan_templates_cpm_lifestyles',
            function (Blueprint $table) {
                $table->unsignedInteger('page')->nullable()->change();
            }
        );
        
        Schema::table(
            'care_plan_templates_cpm_medication_groups',
            function (Blueprint $table) {
                $table->unsignedInteger('page')->nullable()->change();
            }
        );
        
        Schema::table(
            'care_plan_templates_cpm_miscs',
            function (Blueprint $table) {
                $table->unsignedInteger('page')->nullable()->change();
            }
        );
        
        Schema::table(
            'care_plan_templates_cpm_problems',
            function (Blueprint $table) {
                $table->unsignedInteger('page')->nullable()->change();
            }
        );
        
        Schema::table(
            'care_plan_templates_cpm_symptoms',
            function (Blueprint $table) {
                $table->unsignedInteger('page')->nullable()->change();
            }
        );
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
