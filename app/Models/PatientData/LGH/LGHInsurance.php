<?php

namespace App\Models\PatientData\LGH;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PatientData\LGH\LGHInsurance
 *
 * @property int $id
 * @property int|null $mrn
 * @property int|null $fin
 * @property string|null $primary_insurance
 * @property string|null $primary_policy_nbr
 * @property string|null $primary_policy_type
 * @property string|null $primary_subscriber
 * @property string|null $primary_relation
 * @property string|null $secondary_insurance
 * @property string|null $secondary_policy_nbr
 * @property string|null $secondary_policy_type
 * @property string|null $secondary_subscriber
 * @property string|null $secondary_relation
 * @property string|null $tertiary_insurance
 * @property string|null $tertiary_policy_nbr
 * @property string|null $tertiary_policy_type
 * @property string|null $tertiary_subscriber
 * @property string|null $tertiary_relation
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHInsurance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHInsurance whereFin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHInsurance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHInsurance whereMrn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHInsurance wherePrimaryInsurance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHInsurance wherePrimaryPolicyNbr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHInsurance wherePrimaryPolicyType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHInsurance wherePrimaryRelation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHInsurance wherePrimarySubscriber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHInsurance whereSecondaryInsurance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHInsurance whereSecondaryPolicyNbr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHInsurance whereSecondaryPolicyType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHInsurance whereSecondaryRelation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHInsurance whereSecondarySubscriber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHInsurance whereTertiaryInsurance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHInsurance whereTertiaryPolicyNbr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHInsurance whereTertiaryPolicyType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHInsurance whereTertiaryRelation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHInsurance whereTertiarySubscriber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHInsurance whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class LGHInsurance extends \App\BaseModel
{
    protected $table = 'lgh_insurance';

    protected $guarded = [];
}
