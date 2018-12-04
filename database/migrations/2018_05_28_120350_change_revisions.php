<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeRevisions extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('revisions', function (Blueprint $table) {
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('revisions', function (Blueprint $table) {
            $table->dateTime('created_at')->nullable()->default(null)->change();
            $table->dateTime('updated_at')->nullable()->default(null)->change();
        });
    }
}
