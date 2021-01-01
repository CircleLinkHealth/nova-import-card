<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\Eligibility\SelfEnrollment\Notifications\SelfEnrollmentInviteNotification;
use Illuminate\Database\Migrations\Migration;

class RefactorSmsEmailNotifications extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::table('notifications')
            ->whereIn('notifiable_type', [
                'App\Notifications\SendEnrollmentEmail',
                'App\Notifications\SendEnrollementSms',
            ])
            ->update(
                [
                    'notifiable_type' => SelfEnrollmentInviteNotification::class,
                ]
            );
    }
}
