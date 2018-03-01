<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RelateUsersToSaasAccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( ! Schema::hasColumn('users', 'saas_account_id')) {
            Schema::table('users', function (Blueprint $table) {
                //making column nullable to make this change the least invasive it can be
                $table->unsignedInteger('saas_account_id')
                      ->after('id')
                      ->nullable();

                $table->foreign('saas_account_id')
                      ->references('id')
                      ->on('saas_accounts')
                      ->onUpdate('cascade');
            });
        }

        if ( ! Schema::hasColumn('practices', 'saas_account_id')) {
            Schema::table('practices', function (Blueprint $table) {
                //making column nullable to make this change the least invasive it can be
                $table->unsignedInteger('saas_account_id')
                      ->after('id')
                      ->nullable();

                $table->foreign('saas_account_id')
                      ->references('id')
                      ->on('saas_accounts')
                      ->onUpdate('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
}
