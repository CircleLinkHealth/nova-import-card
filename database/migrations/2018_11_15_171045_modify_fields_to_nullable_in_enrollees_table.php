<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyFieldsToNullableInEnrolleesTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('enrollees', function (Blueprint $table) {
            $table->string('other_phone')->nullable(false)->change();
            $table->string('home_phone')->nullable(false)->change();
            $table->text('invite_code', 65535)->nullable(false)->change();
            $table->string('status')->nullable(false)->change();
            $table->integer('attempt_count')->unsigned()->nullable(false)->change();
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('enrollees', function (Blueprint $table) {
            $table->string('other_phone')->nullable()->change();
            $table->string('home_phone')->nullable()->change();
            $table->text('invite_code', 65535)->nullable()->change();
            $table->string('status')->nullable()->change();
            $table->integer('attempt_count')->unsigned()->nullable()->change();
        });
    }
}
