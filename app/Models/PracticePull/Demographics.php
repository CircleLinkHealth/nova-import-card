<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Models\PracticePull;

use CircleLinkHealth\Core\Entities\BaseModel;

/**
 * App\Models\PracticePull\Demographics.
 *
 * @property string                                                                                      $mrn
 * @property string|null                                                                                 $first_name
 * @property string|null                                                                                 $last_name
 * @property \Illuminate\Support\Carbon|null                                                             $dob
 * @property \Illuminate\Support\Carbon|null                                                             $last_encounter
 * @property string|null                                                                                 $gender
 * @property string|null                                                                                 $lang
 * @property string|null                                                                                 $referring_provider_name
 * @property string|null                                                                                 $cell_phone
 * @property string|null                                                                                 $home_phone
 * @property string|null                                                                                 $other_phone
 * @property string|null                                                                                 $primary_phone
 * @property string|null                                                                                 $email
 * @property string|null                                                                                 $street
 * @property string|null                                                                                 $street2
 * @property string|null                                                                                 $city
 * @property string|null                                                                                 $state
 * @property string|null                                                                                 $zip
 * @property string|null                                                                                 $primary_insurance
 * @property string|null                                                                                 $secondary_insurance
 * @property string|null                                                                                 $tertiary_insurance
 * @property int                                                                                         $id
 * @property int|null                                                                                    $location_id
 * @property int|null                                                                                    $billing_provider_user_id
 * @property int                                                                                         $practice_id
 * @property \Illuminate\Support\Carbon|null                                                             $created_at
 * @property \Illuminate\Support\Carbon|null                                                             $updated_at
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @property int|null                                                                                    $revision_history_count
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Models\PracticePull\Demographics newModelQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Models\PracticePull\Demographics newQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Models\PracticePull\Demographics query()
 * @mixin \Eloquent
 * @property int|null $eligibility_job_id
 */
class Demographics extends BaseModel
{
    protected $dates = [
        'dob', 'last_encounter',
    ];
    protected $fillable = [
        'mrn',
        'first_name',
        'last_name',
        'last_encounter',
        'dob',
        'gender',
        'lang',
        'referring_provider_name',
        'facility_name',
        'cell_phone',
        'home_phone',
        'other_phone',
        'primary_phone',
        'email',
        'street',
        'street2',
        'city',
        'state',
        'zip',
        'primary_insurance',
        'secondary_insurance',
        'tertiary_insurance',
        'location_id',
        'billing_provider_user_id',
        'practice_id',
        'eligibility_job_id',
    ];
    protected $table = 'practice_pull_demographics';
}
