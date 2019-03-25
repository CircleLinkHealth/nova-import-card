<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Schema;

class CreateFamiliesTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('families');
    }
    
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(
            'families',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->nullable();
                $table->integer('created_by')->unsigned()->nullable();
                $table->timestamps();
            }
        );
    }
}
