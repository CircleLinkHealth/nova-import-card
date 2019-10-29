<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\Core\Entities\BaseModel;

/**
 * App\ProblemCodeSystem.
 *
 * @property int                 $id
 * @property string              $name
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null         $deleted_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProblemCodeSystem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProblemCodeSystem whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProblemCodeSystem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProblemCodeSystem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProblemCodeSystem whereUpdatedAt($value)
 * @mixin \Eloquent
 *
 * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProblemCodeSystem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProblemCodeSystem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProblemCodeSystem query()
 *
 * @property int|null $revision_history_count
 */
class ProblemCodeSystem extends BaseModel
{
    public $fillable = ['name'];
}
