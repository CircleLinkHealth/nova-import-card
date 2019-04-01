<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\Core\Entities\BaseModel;

/**
 * App\ProcessedFile.
 *
 * @property int                 $id
 * @property string              $path
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProcessedFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProcessedFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProcessedFile wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProcessedFile whereUpdatedAt($value)
 * @mixin \Eloquent
 *
 * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProcessedFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProcessedFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProcessedFile query()
 */
class ProcessedFile extends BaseModel
{
    protected $fillable = [
        'path', // the path to the file processed by the worker
    ];
}
