<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Models\PatientData\LGH;

/**
 * App\Models\PatientData\LGH\LGHProvider.
 *
 * @property string|null         $mrn
 * @property string|null         $last_name
 * @property string|null         $first_name
 * @property string|null         $dob
 * @property string|null         $att_phys
 * @property string|null         $medical_record_type
 * @property int|null            $medical_record_id
 * @property int                 $id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHProvider whereAttPhys($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHProvider whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHProvider whereDob($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHProvider whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHProvider whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHProvider whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHProvider whereMedicalRecordId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHProvider whereMedicalRecordType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHProvider whereMrn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHProvider whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class LGHProvider extends \CircleLinkHealth\Core\Entities\BaseModel
{
    protected $guarded = [];
    protected $table   = 'lgh_providers';
}
