<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInviteOpenedAtToEnrollees extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('enrollees', function (Blueprint $table) {

            $table->dateTime('invite_opened_at')->after('last_attempt_at')->nullable();
            $table->dateTime('last_attempt_at')->nullable(true)->change();
            $table->dateTime('consented_at')->nullable(true)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('enrollees', function (Blueprint $table) {

            $table->dropColumn('invite_opened_at');
        });
    }
}
