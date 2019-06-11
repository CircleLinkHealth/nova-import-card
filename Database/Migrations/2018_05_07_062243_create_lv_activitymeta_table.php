<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLvActivitymetaTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('lv_activitymeta');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(
            'lv_activitymeta',
            function (Blueprint $table) {
                $table->increments('id');
                $table->integer('activity_id')->unsigned()->index('activity_id');
                $table->integer('comment_id')->unsigned();
                $table->string('message_id', 30);
                $table->string('meta_key')->nullable()->index('lv_activitymeta_meta_key');
                $table->text('meta_value');
                $table->timestamps();
                $table->softDeletes();
            }
        );
    }
}
