<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddsIndices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('care_items', function (Blueprint $table) {
            $table->index(['qid']);
        });

        Schema::table('lv_observations', function (Blueprint $table) {
            $table->index(['user_id']);
        });

        Schema::table('lv_observationmeta', function (Blueprint $table) {
            $table->index(['obs_id', 'meta_key']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('care_items', function (Blueprint $table) {
            $table->dropIndex(['qid']);
        });

        Schema::table('lv_observations', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });

        Schema::table('lv_observationmeta', function (Blueprint $table) {
            $table->dropIndex(['obs_id', 'meta_key']);
        });


    }
}
