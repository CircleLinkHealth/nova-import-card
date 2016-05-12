<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMiscMigrHelpers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cpm_miscs', function (Blueprint $table) {
            $table->unsignedInteger('details_care_item_id')->after('id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cpm_miscs', function (Blueprint $table) {
            $table->dropColumn('details_care_item_id');
        });
    }
}
