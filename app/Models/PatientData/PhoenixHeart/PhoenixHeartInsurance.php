<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Models\PatientData\PhoenixHeart;

/**
 * App\Models\PatientData\PhoenixHeart\PhoenixHeartInsurance.
 *
 * @property string|null         $patient_id
 * @property int|null            $order
 * @property string|null         $name
 * @property string|null         $list_name
 * @property int|null            $processed
 * @property \Carbon\Carbon|null $created_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartInsurance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartInsurance whereListName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartInsurance whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartInsurance whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartInsurance wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartInsurance whereProcessed($value)
 * @mixin \Eloquent
 */
class PhoenixHeartInsurance extends \CircleLinkHealth\Core\Entities\BaseModel
{
}
