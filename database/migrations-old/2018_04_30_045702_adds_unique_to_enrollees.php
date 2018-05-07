<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddsUniqueToEnrollees extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('enrollees', function (Blueprint $table) {
            $table->unique(['practice_id', 'mrn']);
            $table->unique(['practice_id', 'first_name', 'last_name', 'dob']);
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
            $table->dropUnique(['practice_id', 'mrn']);
            $table->dropUnique(['practice_id', 'first_name', 'last_name', 'dob']);
        });
    }
}
