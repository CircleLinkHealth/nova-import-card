<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Synonyms\Traits;

use CircleLinkHealth\Synonyms\Entities\Synonym;

trait Synonymable
{
    /**
     * Get all of this Model's comments.
     */
    public function synonyms()
    {
        return $this->morphMany(Synonym::class, 'synonymable');
    }
}
