<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCcdasJsonTableIfNotExists extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ccdas-json');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( ! Schema::hasTable('ccdas-json')) {
            Schema::create('ccdas-json', function (Blueprint $table) {
                $table->bigIncrements('id')->unsigned();
                $table->integer('ccda_id')->nullable(false);
                $table->json('result')->nullable(true);
                $table->string('error')->nullable(true);
                $table->string('status')->nullable(false);
                $table->integer('duration_seconds')->nullable(false)->default(0);
                $table->timestamps();
            });
        }
    }
}
