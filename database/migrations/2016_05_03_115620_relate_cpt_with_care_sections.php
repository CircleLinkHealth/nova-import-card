<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RelateCptWithCareSections extends Migration
{
    private $tables = [
        'care_plan_templates_cpm_biometrics',
        'care_plan_templates_cpm_lifestyles',
        'care_plan_templates_cpm_medication_groups',
        'care_plan_templates_cpm_miscs',
        'care_plan_templates_cpm_problems',
        'care_plan_templates_cpm_symptoms',
    ];
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach ($this->tables as $t)
        {
            Schema::table($t, function (Blueprint $table) {
                $table->unsignedInteger('page')->after('id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach ($this->tables as $t)
        {
            if (! Schema::hasColumn($t, 'page')) continue;
            
            Schema::table($t, function (Blueprint $table) {
                $table->dropColumn('page');
            });
        }
    }
}
