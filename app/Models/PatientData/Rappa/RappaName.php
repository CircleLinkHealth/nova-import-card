<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Models\PatientData\Rappa;

/**
 * App\Models\PatientData\Rappa\RappaName.
 *
 * @property int|null    $patient_id
 * @property string|null $email
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $home_phone
 * @property string|null $primary_phone
 * @property string|null $work_phone
 * @property string|null $preferred_contact_method
 * @property string|null $preferred_provider
 * @property string|null $address_1
 * @property string|null $address_2
 * @property string|null $city
 * @property string|null $state
 * @property string|null $zip
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaName whereAddress1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaName whereAddress2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaName whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaName whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaName whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaName whereHomePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaName whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaName wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaName wherePreferredContactMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaName wherePreferredProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaName wherePrimaryPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaName whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaName whereWorkPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaName whereZip($value)
 * @mixin \Eloquent
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaName newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaName newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaName query()
 * @property int|null $revision_history_count
 */
class RappaName extends \CircleLinkHealth\Core\Entities\BaseModel
{
    public $guarded = [];
}
