<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Synonyms\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * CircleLinkHealth\Synonyms\Entities\Synonym.
 *
 * @property int                             $id
 * @property string                          $synonymable_type
 * @property int                             $synonymable_id
 * @property string                          $column
 * @property string                          $synonym
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Eloquent|Model                 $synonymable
 * @method static                          \Illuminate\Database\Eloquent\Builder|Synonym newModelQuery()
 * @method static                          \Illuminate\Database\Eloquent\Builder|Synonym newQuery()
 * @method static                          \Illuminate\Database\Eloquent\Builder|Synonym query()
 * @mixin \Eloquent
 */
class Synonym extends Model
{
    protected $fillable = [
        'synonymable_type',
        'synonymable_id',
        'column',
        'synonym',
    ];

    /**
     * Get the owning synonymable model.
     */
    public function synonymable()
    {
        return $this->morphTo();
    }
}
