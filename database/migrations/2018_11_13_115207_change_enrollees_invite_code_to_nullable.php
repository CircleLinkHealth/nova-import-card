<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeEnrolleesInviteCodeToNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('enrollees', function ($table) {
            $table->text('invite_code')->nullable(true)->change();
            $table->string('status')->nullable(true)->change();
            $table->integer('attempt_count')->nullable(true)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('enrollees', function ($table) {
            $table->text('invite_code')->nullable(false)->change();
            $table->string('status')->nullable(false)->change();
            $table->integer('attempt_count')->nullable(false)->change();
        });
    }
}
