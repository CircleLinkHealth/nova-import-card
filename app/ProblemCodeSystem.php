<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\ProblemCodeSystem
 *
 * @property int $id
 * @property string $name
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProblemCodeSystem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProblemCodeSystem whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProblemCodeSystem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProblemCodeSystem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProblemCodeSystem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ProblemCodeSystem extends Model
{
    public $fillable = ['name'];
}
