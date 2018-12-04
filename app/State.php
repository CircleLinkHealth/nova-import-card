<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\State
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Nurse[] $nurses
 * @method static \Illuminate\Database\Eloquent\Builder|\App\State whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\State whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\State whereName($value)
 * @mixin \Eloquent
 */
class State extends \App\BaseModel
{
    public $timestamps = false;

    public function nurses()
    {
        return $this->belongsToMany(Nurse::class);
    }
}
