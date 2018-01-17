<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 01/17/2018
 * Time: 12:44 AM
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

    /**
     * Scope for scheduled calls
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scheduled()
    {
        return $this->builder->scheduled();
    }

    /**
     * Scope for calls by Caller name.
     *
     * @param $term
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function caller($term)
    {
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
    public function patientId($id)
    {
        return $this->builder
            ->whereHas('inboundUser', function ($q) use ($id) {
                $q->where('id', '=', $id);
            });
    }

    /**
     * Scope for calls by patient name.
     *
     * @param $name
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function patientName($name)
    {
        return $this->builder
            ->whereHas('inboundUser', function ($q) use ($name) {
                $q->where('display_name', 'like', "%$name%");
            });
    }

    /**
     * Scope for calls by scheduled date.
     *
     * @param $date
     *
     * @return \Illuminate\Database\Eloquent\Builder
     * @throws \Exception
     */
    public function scheduledDate($date)
    {
        validateYYYYMMDDDateString($date);

        return $this->builder
            ->whereScheduledDate($date);
    }

    /**
     * Scope for calls by the date the patient was last called.
     *
     * @param $date
     *
     * @return \Illuminate\Database\Eloquent\Builder
     * @throws \Exception
     */
    public function lastCallDate($date) {
        validateYYYYMMDDDateString($date);

        return $this->builder
            ->whereHas('inboundUser.patientInfo', function ($q) use ($date) {
                $q->whereLastContactTime($date);
            });
    }

    public function globalFilters(): array
    {
        return [];
    }
}