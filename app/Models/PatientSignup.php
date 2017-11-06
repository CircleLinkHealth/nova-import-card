<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PatientSignup
 *
 * @property int $id
 * @property string $name
 * @property string $phone
 * @property string $email
 * @property string $comment
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientSignup whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientSignup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientSignup whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientSignup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientSignup whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientSignup wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientSignup whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PatientSignup extends \App\BaseModel
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'comment',
    ];
}
