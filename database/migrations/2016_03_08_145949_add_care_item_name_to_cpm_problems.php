<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddCareItemNameToCpmProblems extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cpm_problems', function (Blueprint $table) {
            if (!Schema::hasColumn('cpm_problems', 'care_item_name')) {
                $table->string('care_item_name')->nullable();
                $table->foreign('care_item_name')->references('name')->on('care_items');
            }

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cpm_problems', function (Blueprint $table) {
            $table->dropForeign(['care_item_name']);
            $table->dropColumn('care_item_name');
        });


    }

}
