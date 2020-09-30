<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Synonyms\Traits;

use CircleLinkHealth\Synonyms\Entities\Synonym;

trait Synonymable
{
    public function scopeWhereColumnOrSynonym($builder, $column, $synonym)
    {
        $builder->where(function ($q) use ($column, $synonym) {
            $q->where('name', $synonym)
                ->orWhereHas('synonyms', function ($q) use ($column, $synonym) {
                    $q->where('column', $column)
                        ->where('synonym', $synonym);
                });
        });
    }

    /**
     * Get all of this Model's comments.
     */
    public function synonyms()
    {
        return $this->morphMany(Synonym::class, 'synonymable');
    }
}
