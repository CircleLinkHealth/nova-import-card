<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;

trait WithNonImported
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     */
    public function scopeWithNonImported(Builder $builder)
    {
        $builder->withoutGlobalScope(Imported::class);
    }
}
