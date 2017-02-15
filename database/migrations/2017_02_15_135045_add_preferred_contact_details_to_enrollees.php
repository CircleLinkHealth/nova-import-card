<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPreferredContactDetailsToEnrollees extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('enrollees', function (Blueprint $table) {

            $table->string('preferred_days')->after('attempt_count')->nullable();
            $table->string('preferred_window')->after('preferred_days')->nullable();

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

            $table->dropColumn('preferred_days');
            $table->dropColumn('preferred_window');

        });
    }
}
