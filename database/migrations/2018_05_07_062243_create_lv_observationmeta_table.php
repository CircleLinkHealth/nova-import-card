<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLvObservationmetaTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('lv_observationmeta');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('lv_observationmeta', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('obs_id')->unsigned();
            $table->integer('comment_id')->unsigned();
            $table->string('message_id', 30);
            $table->string('meta_key', 50);
            $table->string('meta_value');
            $table->integer('program_id')->unsigned();
            $table->integer('legacy_meta_id')->unsigned();
            $table->timestamps();
            $table->index(['obs_id', 'meta_key'], 'lv_observationmeta_obs_id_meta_key');
        });
    }
}
