<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Models\PatientData\PhoenixHeart;

/**
 * App\Models\PatientData\PhoenixHeart\PhoenixHeartAllergy.
 *
 * @property string|null         $patient_id
 * @property string|null         $name
 * @property string|null         $description
 * @property string|null         $start_date
 * @property string|null         $end_date
 * @property string|null         $stop_reason
 * @property int|null            $processed
 * @property \Carbon\Carbon|null $created_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartAllergy whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartAllergy whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartAllergy whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartAllergy whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartAllergy wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartAllergy whereProcessed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartAllergy whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartAllergy whereStopReason($value)
 * @mixin \Eloquent
 * @property \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Revisionable\Entities\Revision[] $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartAllergy newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartAllergy newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartAllergy query()
 * @property int|null $revision_history_count
 */
class PhoenixHeartAllergy extends \CircleLinkHealth\Core\Entities\BaseModel
{
}
