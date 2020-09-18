<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Filters;

use App\Repositories\NoteRepository;
use CircleLinkHealth\Core\Filters\QueryFilters;
use Illuminate\Http\Request;

class NoteFilters extends QueryFilters
{
    protected $request;
    private $noteRepository;

    public function __construct(Request $request, NoteRepository $noteRepository)
    {
        $this->request        = $request;
        $this->noteRepository = $noteRepository;
        parent::__construct($request);
    }

    public function globalFilters(): array
    {
        return [];
    }

    public function sort_createdAt($type = null)
    {
        return $this->builder->orderBy('created_at', $type);
    }

    public function sort_id($type = null)
    {
        return $this->builder->orderBy('id', $type);
    }

    public function type($term)
    {
        return $this->builder->where('type', $term);
    }
}
