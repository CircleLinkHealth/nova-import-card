<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConditionCodeLookupTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('condition_code_lookup_cache');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('condition_code_lookup_cache', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('type');
            $table->string('code');
            $table->string('name');
            $table->string('request_url');
            $table->string('response_raw');

            $table->timestamps();
        });
    }
}
