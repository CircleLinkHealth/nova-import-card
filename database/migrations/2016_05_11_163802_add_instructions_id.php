<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInstructionsId extends Migration
{
    protected $tables = [
        'care_plan_templates_cpm_biometrics',
        'care_plan_templates_cpm_lifestyles',
        'care_plan_templates_cpm_medication_groups',
        'care_plan_templates_cpm_miscs',
        'care_plan_templates_cpm_problems',
        'care_plan_templates_cpm_symptoms',
        'cpm_biometrics_users',
        'cpm_lifestyles_users',
        'cpm_medication_groups_users',
        'cpm_miscs_users',
        'cpm_problems_users',
        'cpm_symptoms_users',
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach ($this->tables as $t) {

            if (Schema::hasColumn($t, 'cpm_instruction_id')) continue;

            Schema::table($t, function (Blueprint $table) use ($t) {
                $table->unsignedInteger('cpm_instruction_id')->after('id')->nullable();

                if ($t == 'care_plan_templates_cpm_medication_groups') {
                    $table->foreign('cpm_instruction_id', "{$t}_instrction_foreign")
                        ->references('id')
                        ->on((new \App\Models\CPM\CpmInstruction())->getTable())
                        ->onUpdate('cascade')
                        ->onDelete('set null');
                } else {
                    $table->foreign('cpm_instruction_id')
                        ->references('id')
                        ->on((new \App\Models\CPM\CpmInstruction())->getTable())
                        ->onUpdate('cascade')
                        ->onDelete('set null');
                }

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
        DB::statement('SET foreign_key_checks = 0');

        foreach ($this->tables as $t) {

            if (!Schema::hasColumn($t, 'cpm_instruction_id')) continue;

            Schema::table($t, function (Blueprint $table) use ($t) {
                if ($t == 'care_plan_templates_cpm_medication_groups') {
                    $table->dropForeign("{$t}_instrction_foreign");
                } else {
                    $table->dropForeign(['cpm_instruction_id']);
                }

                $table->dropColumn('cpm_instruction_id');

            });
        }
        DB::statement('SET foreign_key_checks = 1');

    }
}
