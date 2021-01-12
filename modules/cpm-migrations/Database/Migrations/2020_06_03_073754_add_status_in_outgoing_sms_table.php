<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusInOutgoingSmsTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('outgoing_sms', 'status')) {
            Schema::table('outgoing_sms', function (Blueprint $table) {
                $table->dropColumn('status_details');
                $table->dropColumn('status');
                $table->dropColumn('sid');
                $table->dropColumn('account_sid');
            });
        }
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( ! Schema::hasColumn('outgoing_sms', 'status')) {
            Schema::table('outgoing_sms', function (Blueprint $table) {
                $table->string('account_sid')->after('message');
                $table->string('sid')->after('message');
                $table->string('status_details')->after('message');
                $table->string('status')->after('message');
            });
        }
    }
}
