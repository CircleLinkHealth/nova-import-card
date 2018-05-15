<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFirstPrinted extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('care_plans', function (Blueprint $table) {
            $table->dateTime('first_printed')
                  ->nullable()
                  ->after('provider_date');

            $table->unsignedInteger('first_printed_by')->nullable()->after('provider_date');

            $table->foreign('first_printed_by')
                  ->references('id')
                  ->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('care_plans', function (Blueprint $table) {
            //
        });
    }
}
