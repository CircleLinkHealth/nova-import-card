<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangePracticeUserUniqueKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('practice_user', function (Blueprint $table) {
            $table->dropUnique('practice_user_program_id_user_id_unique');

            $table->unique(['user_id', 'role_id', 'program_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('practice_user', function (Blueprint $table) {
            $table->dropUnique([['user_id', 'role_id', 'program_id']]);
        });
    }
}
