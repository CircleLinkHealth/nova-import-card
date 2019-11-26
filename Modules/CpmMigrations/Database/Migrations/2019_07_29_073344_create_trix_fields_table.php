<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrixFieldsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('trix_fields');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('trix_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type');
            $table->string('language');
            $table->text('body');
            $table->timestamps();
        });
    }
}
