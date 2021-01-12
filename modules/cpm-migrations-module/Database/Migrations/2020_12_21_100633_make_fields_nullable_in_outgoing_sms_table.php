<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeFieldsNullableInOutgoingSmsTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('outgoing_sms', function (Blueprint $table) {
            $table->string('status')->nullable(false)->change();
            $table->string('status_details')->nullable(false)->change();
            $table->string('sid')->nullable(false)->change();
            $table->string('account_sid')->nullable(false)->change();
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('outgoing_sms', function (Blueprint $table) {
            $table->string('status')->nullable(true)->change();
            $table->string('status_details')->nullable(true)->change();
            $table->string('sid')->nullable(true)->change();
            $table->string('account_sid')->nullable(true)->change();
        });
    }
}
