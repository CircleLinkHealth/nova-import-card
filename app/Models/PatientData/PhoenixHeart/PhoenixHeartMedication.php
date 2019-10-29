<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Models\PatientData\PhoenixHeart;

/**
 * App\Models\PatientData\PhoenixHeart\PhoenixHeartMedication.
 *
 * @property string|null         $patient_id
 * @property string|null         $description
 * @property string|null         $instructions
 * @property string|null         $start_date
 * @property string|null         $end_date
 * @property string|null         $stop_reason
 * @property int|null            $processed
 * @property \Carbon\Carbon|null $created_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartMedication whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartMedication whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartMedication whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartMedication whereInstructions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartMedication wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartMedication whereProcessed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartMedication whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartMedication whereStopReason($value)
 * @mixin \Eloquent
 *
 * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartMedication newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartMedication newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartMedication query()
 *
 * @property int|null $revision_history_count
 */
class PhoenixHeartMedication extends \CircleLinkHealth\Core\Entities\BaseModel
{
}
