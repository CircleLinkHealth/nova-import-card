<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpgradePassport extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('oauth_clients', 'provider')) {
            return;
        }
        Schema::table(
            'oauth_clients',
            function (Blueprint $table) {
                $table->string('provider')->after('secret')->nullable();
            }
        );
    }
}