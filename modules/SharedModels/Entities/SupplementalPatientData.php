<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Entities;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Database\Eloquent\Model;

/**
 * CircleLinkHealth\SharedModels\Entities\SupplementalPatientData.
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
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\Models\SupplementalPatientData\NBI\SupplementalPatientData newModelQuery()
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\Models\SupplementalPatientData\NBI\SupplementalPatientData newQuery()
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\Models\SupplementalPatientData\NBI\SupplementalPatientData query()
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\Models\SupplementalPatientData\NBI\SupplementalPatientData whereCreatedAt($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\Models\SupplementalPatientData\NBI\SupplementalPatientData whereDob($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\Models\SupplementalPatientData\NBI\SupplementalPatientData whereFirstName($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\Models\SupplementalPatientData\NBI\SupplementalPatientData whereId($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\Models\SupplementalPatientData\NBI\SupplementalPatientData whereLastName($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\Models\SupplementalPatientData\NBI\SupplementalPatientData whereMrn($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\Models\SupplementalPatientData\NBI\SupplementalPatientData wherePrimaryInsurance($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\Models\SupplementalPatientData\NBI\SupplementalPatientData whereProvider($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\Models\SupplementalPatientData\NBI\SupplementalPatientData whereSecondaryInsurance($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\Models\SupplementalPatientData\NBI\SupplementalPatientData whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int                                          $practice_id
 * @property int|null                                     $location_id
 * @property int|null                                     $billing_provider_user_id
 * @property string|null                                  $location
 * @property \CircleLinkHealth\Customer\Entities\Practice $practice
 * @method   static                                       \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\SupplementalPatientData whereBillingProviderUserId($value)
 * @method   static                                       \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\SupplementalPatientData whereLocation($value)
 * @method   static                                       \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\SupplementalPatientData whereLocationId($value)
 * @method   static                                       \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\SupplementalPatientData wherePracticeId($value)
 */
class SupplementalPatientData extends Model
{
    protected $dates = [
        'dob',
    ];

    protected $fillable = [
        'practice_id',
        'location_id',
        'billing_provider_user_id',
        'dob',
        'first_name',
        'last_name',
        'mrn',
        'primary_insurance',
        'secondary_insurance',
        'provider',
        'location',
    ];
    protected $table = 'supplemental_patient_data';

    public static function forPatient(?int $practiceId, ?string $firstName, ?string $lastName, ?Carbon $dob)
    {
        if ( ! $dob || ! $practiceId || ! $firstName || ! $lastName) {
            return null;
        }

        return SupplementalPatientData::where(
            'first_name',
            'like',
            "$firstName%"
        )
            ->where('practice_id', $practiceId)
            ->where(
                'last_name',
                $lastName
            )->where('dob', $dob)->first();
    }

    public function practice()
    {
        return $this->belongsTo(Practice::class);
    }
}
