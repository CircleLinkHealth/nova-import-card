<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLvPasswordResetsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('lv_password_resets');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(
            'lv_password_resets',
            function (Blueprint $table) {
                $table->string('email')->index('password_resets_email_index');
                $table->string('token')->index('password_resets_token_index');
                $table->dateTime('created_at')->nullable();
            }
        );
    }
}
