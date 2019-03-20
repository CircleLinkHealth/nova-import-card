<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLastSessionIdInUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( ! Schema::hasColumn('users', 'last_session_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('last_session_id')->nullable();
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
        if (Schema::hasColumn('users', 'last_session_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('last_session_id');
            });
        }
    }
}
