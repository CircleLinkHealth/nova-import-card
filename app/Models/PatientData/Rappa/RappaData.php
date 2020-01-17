<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Models\PatientData\Rappa;

/**
 * App\Models\PatientData\Rappa\RappaData.
 *
 * @property string|null $last_encounter
 * @property string|null $last_name
 * @property string|null $first_name
 * @property string|null $patient_id
 * @property string|null $note
 * @property string|null $medication
 * @property string|null $condition
 * @property string|null $provider
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaData whereCondition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaData whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaData whereLastEncounter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaData whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaData whereMedication($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaData whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaData wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaData whereProvider($value)
 * @mixin \Eloquent
 * @property \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Revisionable\Entities\Revision[] $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaData newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaData newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaData query()
 * @property int|null $revision_history_count
 */
class RappaData extends \CircleLinkHealth\Core\Entities\BaseModel
{
    public $guarded = [];
}
