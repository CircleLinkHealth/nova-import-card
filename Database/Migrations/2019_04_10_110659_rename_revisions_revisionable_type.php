<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class RenameRevisionsRevisionableType extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        //I dont think we can reverse this easily since there were already some entries from the following module entities
//        CircleLinkHealth\Customer\Entities\Appointment
//        CircleLinkHealth\Customer\Entities\CarePerson
//        CircleLinkHealth\Customer\Entities\Nurse
//        CircleLinkHealth\Customer\Entities\NurseCareRateLog
//        CircleLinkHealth\Customer\Entities\NurseContactWindow
//        CircleLinkHealth\Customer\Entities\NurseMonthlySummary
//        CircleLinkHealth\Customer\Entities\Patient
//        CircleLinkHealth\Customer\Entities\PatientContactWindow
//        CircleLinkHealth\Customer\Entities\PatientMonthlySummary
//        CircleLinkHealth\Customer\Entities\PhoneNumber
//        CircleLinkHealth\Customer\Entities\PracticeRoleUser
//        CircleLinkHealth\Customer\Entities\ProviderInfo
//        CircleLinkHealth\Customer\Entities\User
//        CircleLinkHealth\Customer\Entities\WorkHours
//        CircleLinkHealth\SharedModels\Entities\Activity
//        CircleLinkHealth\SharedModels\Entities\ActivityMeta
//        CircleLinkHealth\SharedModels\Entities\PageTimer
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        \DB::table('revisions')
            ->where('revisionable_type', 'App\Appointment')
            ->update(
                [
                    'revisionable_type' => 'CircleLinkHealth\Customer\Entities\Appointment',
                ]
            );

        \DB::table('revisions')
            ->where('revisionable_type', 'App\CarePerson')
            ->update(
                [
                    'revisionable_type' => 'CircleLinkHealth\Customer\Entities\CarePerson',
                ]
            );

        \DB::table('revisions')
            ->where('revisionable_type', 'App\EmrDirectAddress')
            ->update(
                [
                    'revisionable_type' => 'CircleLinkHealth\Customer\Entities\EmrDirectAddress',
                ]
            );

        \DB::table('revisions')
            ->where('revisionable_type', 'App\Family')
            ->update(
                [
                    'revisionable_type' => 'CircleLinkHealth\Customer\Entities\Family',
                ]
            );

        \DB::table('revisions')
            ->where('revisionable_type', 'App\Location')
            ->update(
                [
                    'revisionable_type' => 'CircleLinkHealth\Customer\Entities\Location',
                ]
            );

        \DB::table('revisions')
            ->where('revisionable_type', 'App\Models\Holiday')
            ->update(
                [
                    'revisionable_type' => 'CircleLinkHealth\Customer\Entities\Holiday',
                ]
            );

        \DB::table('revisions')
            ->where('revisionable_type', 'App\Models\WorkHours')
            ->update(
                [
                    'revisionable_type' => 'CircleLinkHealth\Customer\Entities\WorkHours',
                ]
            );

        \DB::table('revisions')
            ->where('revisionable_type', 'App\Nurse')
            ->update(
                [
                    'revisionable_type' => 'CircleLinkHealth\Customer\Entities\Nurse',
                ]
            );

        \DB::table('revisions')
            ->where('revisionable_type', 'App\NurseCareRateLog')
            ->update(
                [
                    'revisionable_type' => 'CircleLinkHealth\Customer\Entities\NurseCareRateLog',
                ]
            );

        \DB::table('revisions')
            ->where('revisionable_type', 'App\NurseContactWindow')
            ->update(
                [
                    'revisionable_type' => 'CircleLinkHealth\Customer\Entities\NurseContactWindow',
                ]
            );

        \DB::table('revisions')
            ->where('revisionable_type', 'App\NurseMonthlySummary')
            ->update(
                [
                    'revisionable_type' => 'CircleLinkHealth\Customer\Entities\NurseMonthlySummary',
                ]
            );

        \DB::table('revisions')
            ->where('revisionable_type', 'App\Patient')
            ->update(
                [
                    'revisionable_type' => 'CircleLinkHealth\Customer\Entities\Patient',
                ]
            );

        \DB::table('revisions')
            ->where('revisionable_type', 'App\PatientContactWindow')
            ->update(
                [
                    'revisionable_type' => 'CircleLinkHealth\Customer\Entities\PatientContactWindow',
                ]
            );

        \DB::table('revisions')
            ->where('revisionable_type', 'App\PatientMonthlySummary')
            ->update(
                [
                    'revisionable_type' => 'CircleLinkHealth\Customer\Entities\PatientMonthlySummary',
                ]
            );

        \DB::table('revisions')
            ->where('revisionable_type', 'App\PhoneNumber')
            ->update(
                [
                    'revisionable_type' => 'CircleLinkHealth\Customer\Entities\PhoneNumber',
                ]
            );
    }
}
