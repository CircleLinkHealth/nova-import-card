<?php

namespace App\Models\PatientData\PhoenixHeart;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PatientData\PhoenixHeart\PhoenixHeartProblem
 *
 * @property string|null $patient_id
 * @property string|null $code
 * @property string|null $description
 * @property string|null $start_date
 * @property string|null $end_date
 * @property string|null $stop_reason
 * @property int|null $processed
 * @property \Carbon\Carbon|null $created_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartProblem whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartProblem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartProblem whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartProblem whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartProblem wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartProblem whereProcessed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartProblem whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartProblem whereStopReason($value)
 * @mixin \Eloquent
 */
class PhoenixHeartProblem extends \App\BaseModel
{
    //
}
