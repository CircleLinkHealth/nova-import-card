<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Filters;

use App\Note;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class NoteStatusFilter extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    public $name = 'Note Status';

    /**
     * @var string
     */
    private $default;

    /**
     * NoteStatusFilter constructor.
     */
    public function __construct(string $default)
    {
        $this->default = $default;
    }

    /**
     * Apply the filter to the given query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed                                 $value
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {
        return $query->where('status', '=', $value);
    }

    public function default()
    {
        return $this->default;
    }

    /**
     * Get the filter's available options.
     *
     * @return array
     */
    public function options(Request $request)
    {
        return [
            'Draft'    => Note::STATUS_DRAFT,
            'Complete' => Note::STATUS_COMPLETE,
        ];
    }
}
