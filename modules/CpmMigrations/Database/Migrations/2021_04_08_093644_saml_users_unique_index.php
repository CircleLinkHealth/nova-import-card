<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class SamlUsersUniqueIndex extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('saml_users', function (Blueprint $table) {
            $table->dropUnique('idp_id_user_id_unique');
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('saml_users', function (Blueprint $table) {
            $table->unique(['idp', 'idp_user_id'], 'idp_id_user_id_unique');
        });
    }
}
