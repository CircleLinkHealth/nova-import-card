<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeDefaultsToZero extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table(
            'nurse_monthly_summaries',
            function (Blueprint $table) {
                $table->integer('no_of_calls')
                    ->nullable(false)
                    ->default(0)
                    ->change();

                $table->integer('no_of_successful_calls')
                    ->default(0)
                    ->nullable(false)
                    ->change();
            }
        );
    }
}
