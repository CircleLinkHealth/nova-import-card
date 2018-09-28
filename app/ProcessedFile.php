<?php

namespace App;

/**
 * App\ProcessedFile
 *
 * @property int $id
 * @property string $path
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProcessedFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProcessedFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProcessedFile wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProcessedFile whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ProcessedFile extends BaseModel
{
    protected $fillable = [
        'path', // the path to the file processed by the worker
    ];
}
