<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPayMethodToNurseInfoTable extends Migration
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
        Schema::table('nurse_info', function (Blueprint $table) {
            $table->boolean('is_demo')->default(false);
            $table->integer('pay_interval')->default(40);
            $table->boolean('pay_algo')->default(true);
        });
    }
}
