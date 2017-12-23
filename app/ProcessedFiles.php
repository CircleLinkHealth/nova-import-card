<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\ProcessedFiles
 *
 * @property int $id
 * @property string $path
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProcessedFiles whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProcessedFiles whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProcessedFiles wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProcessedFiles whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ProcessedFiles extends \App\BaseModel
{
    protected $fillable = [
        'path', // the path to the file processed by the worker
    ];
}
