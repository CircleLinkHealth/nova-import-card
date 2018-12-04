<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSaasAccountsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('saas_accounts');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('saas_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('slug');
            $table->string('logo_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
