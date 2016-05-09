<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MoreHelperFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tables = [
            (new \App\CarePlanItem())->getTable(),
            (new \App\CareItemUserValue())->getTable(),
            (new \App\CareItem)->getTable(),
        ];

        foreach ($tables as $t) {
            Schema::table($t, function (Blueprint $table) {
                $table->string('model_field_name')->after('id')->nullable();
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
        $tables = [
            (new \App\CarePlanItem())->getTable(),
            (new \App\CareItemUserValue())->getTable(),
            (new \App\CareItem)->getTable(),
        ];

        foreach ($tables as $t) {
            Schema::table($t, function (Blueprint $table) {
                $table->dropColumn('model_field_name');
            });
        }
    }
}
