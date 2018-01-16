<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 01/16/2018
 * Time: 11:18 PM
 */

namespace App\Filters;


use App\Repositories\CallRepository;
use Illuminate\Http\Request;

class CallFilters extends QueryFilters
{
    public function __construct(Request $request, CallRepository $callRepository)
    {
        parent::__construct($request);
    }

    public function globalFilters(): array
    {
        return [];
    }

    /**
     * Scope for scheduled calls
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scheduled() {
        return $this->builder->scheduled();
    }

    /**
     * Scope for calls by Caller name.
     *
     * @param $term
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function caller($term) {
        return $this->builder
            ->whereHas('outboundUser', function ($q) use ($term) {
                $q->where('display_name', 'like', "%$term%");
            });
    }

    /**
     * Scope for calls by patient id.
     *
     * @param $id
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function patientId($id) {
        return $this->builder
            ->whereHas('inboundUser', function ($q) use ($id) {
                $q->where('id', '=', $id);
            });
    }
}