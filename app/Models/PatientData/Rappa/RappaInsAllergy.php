<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Models\PatientData\Rappa;

/**
 * App\Models\PatientData\Rappa\RappaInsAllergy.
 *
 * @property string|null $patient_name
 * @property int|null    $patient_id
 * @property string|null $last_encounter
 * @property string|null $allergy
 * @property string|null $primary_insurance
 * @property string|null $secondary_insurance
 * @property string|null $provider
 * @property string|null $address_1
 * @property string|null $address_2
 * @property string|null $city
 * @property string|null $zip
 * @property string|null $county
 * @property string|null $home_phone
 * @property string|null $primary_phone
 * @property string|null $preferred_contact_method
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaInsAllergy whereAddress1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaInsAllergy whereAddress2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaInsAllergy whereAllergy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaInsAllergy whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaInsAllergy whereCounty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaInsAllergy whereHomePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaInsAllergy whereLastEncounter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaInsAllergy wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaInsAllergy wherePatientName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaInsAllergy wherePreferredContactMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaInsAllergy wherePrimaryInsurance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaInsAllergy wherePrimaryPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaInsAllergy whereProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaInsAllergy whereSecondaryInsurance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaInsAllergy whereZip($value)
 * @mixin \Eloquent
 * @property \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Revisionable\Entities\Revision[] $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaInsAllergy newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaInsAllergy newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaInsAllergy query()
 * @property int|null $revision_history_count
 */
class RappaInsAllergy extends \CircleLinkHealth\Core\Entities\BaseModel
{
    public $guarded = [];
}
