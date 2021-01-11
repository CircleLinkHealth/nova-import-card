<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVirtualColumnsInNotificationsTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('mail_smtp_id');
            $table->dropColumn('mail_sg_message_id');
            $table->dropColumn('mail_status');
            $table->dropColumn('mail_details');
            $table->dropColumn('twilio_sid');
            $table->dropColumn('twilio_account_sid');
            $table->dropColumn('twilio_status');
            $table->dropColumn('twilio_details');
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('twilio_details')
                ->after('data')
                ->virtualAs('JSON_UNQUOTE(data->"$.status.twilio.details")')
                ->index();
            $table->string('twilio_status')
                ->after('data')
                ->virtualAs('JSON_UNQUOTE(data->"$.status.twilio.value")')
                ->index();
            $table->string('twilio_account_sid')
                ->after('data')
                ->virtualAs('JSON_UNQUOTE(data->"$.status.twilio.account_sid")')
                ->index();
            $table->string('twilio_sid')
                ->after('data')
                ->virtualAs('JSON_UNQUOTE(data->"$.status.twilio.sid")')
                ->index();
            $table->string('mail_details')
                ->after('data')
                ->virtualAs('JSON_UNQUOTE(data->"$.status.mail.details")')
                ->index();
            $table->string('mail_status')
                ->after('data')
                ->virtualAs('JSON_UNQUOTE(data->"$.status.mail.value")')
                ->index();
            $table->string('mail_sg_message_id')
                ->after('data')
                ->virtualAs('JSON_UNQUOTE(data->"$.status.mail.sg_message_id")')
                ->index();
            $table->string('mail_smtp_id')
                ->after('data')
                ->virtualAs('JSON_UNQUOTE(data->"$.status.mail.smtp_id")')
                ->index();
        });
    }
}
