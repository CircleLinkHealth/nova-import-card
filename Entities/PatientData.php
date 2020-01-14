<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * CircleLinkHealth\Eligibility\Entities\PatientData.
 *
 * @property int                             $id
 * @property \Illuminate\Support\Carbon      $dob
 * @property string                          $first_name
 * @property string                          $last_name
 * @property string                          $mrn
 * @property string|null                     $provider
 * @property string|null                     $primary_insurance
 * @property string|null                     $secondary_insurance
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\NBI\PatientData newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\NBI\PatientData newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\NBI\PatientData query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\NBI\PatientData whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\NBI\PatientData whereDob($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\NBI\PatientData whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\NBI\PatientData whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\NBI\PatientData whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\NBI\PatientData whereMrn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\NBI\PatientData wherePrimaryInsurance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\NBI\PatientData whereProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\NBI\PatientData whereSecondaryInsurance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\NBI\PatientData whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PatientData extends Model
{
    protected $dates = [
        'dob',
    ];

    protected $fillable = [
        'dob',
        'first_name',
        'last_name',
        'mrn',
        'primary_insurance',
        'provider',
        'secondary_insurance',
    ];
    protected $table = 'patient_data';
}
