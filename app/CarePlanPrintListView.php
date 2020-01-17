<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\Core\Entities\SqlViewModel;

/**
 * App\CarePlanPrintListView.
 *
 * @property int         $care_plan_id
 * @property string      $care_plan_status
 * @property string|null $last_printed
 * @property string|null $provider_date
 * @property int|null    $patient_id
 * @property string|null $patient_full_name
 * @property string|null $patient_first_name
 * @property string|null $patient_last_name
 * @property string|null $patient_registered
 * @property int|null    $patient_info_id
 * @property string|null $patient_dob
 * @property string|null $patient_ccm_status
 * @property int|null    $primary_practice_id
 * @property string|null $practice_name
 * @property string|null $approver_full_name
 * @property string|null $provider_full_name
 * @property int|null    $patient_ccm_time
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanPrintListView newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanPrintListView newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanPrintListView query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanPrintListView whereApproverFullName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanPrintListView whereCarePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanPrintListView whereCarePlanStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanPrintListView whereLastPrinted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanPrintListView wherePatientCcmStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanPrintListView wherePatientCcmTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanPrintListView wherePatientDob($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanPrintListView wherePatientFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanPrintListView wherePatientFullName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanPrintListView wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanPrintListView wherePatientInfoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanPrintListView wherePatientLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanPrintListView wherePatientRegistered($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanPrintListView wherePracticeName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanPrintListView wherePrimaryPracticeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanPrintListView whereProviderDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanPrintListView whereProviderFullName($value)
 * @mixin \Eloquent
 *
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @property int|null                                                                                    $revision_history_count
 */
class CarePlanPrintListView extends SqlViewModel
{
    public $phi = [
        'patient_full_name',
        'patient_first_name',
        'patient_last_name',
    ];

    protected $table = 'careplan_print_list_view';
}
