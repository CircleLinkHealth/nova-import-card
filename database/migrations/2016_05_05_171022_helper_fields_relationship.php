<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class HelperFieldsRelationship extends Migration
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
                $table->string('relationship_fn_name')->after('type');
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
                $table->dropColumn('relationship_fn_name');
            });
        }
    }
}
