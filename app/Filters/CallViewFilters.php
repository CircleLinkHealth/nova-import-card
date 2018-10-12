<?php
/**
 * Created by IntelliJ IDEA.
 * User: pangratioscosma
 * Date: 12/09/2018
 * Time: 15:42
 */

namespace App\Filters;


use Illuminate\Http\Request;

class CallViewFilters extends QueryFilters
{

    /**
     * CallViewFilters constructor.
     * Sorting and filters just work, simply because
     * the column names match the view table names and they
     * are ordered by them.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function unassigned()
    {
        return $this->builder->whereNull('nurse_id');
    }

    public function type($type)
    {
        return $this->builder->where('type', 'like', '%' . $type . '%');
    }

    public function nurse($nurse)
    {
        return $this->builder->where('nurse', 'like', '%' . $nurse . '%');
    }

    public function patient_id($id)
    {
        return $this->builder->where('patient_id', '=', $id);
    }

    public function scheduled_date($date)
    {
        return $this->builder->where('scheduled_date', 'like', '%' . $date . '%');
    }

    public function last_call($lastCall)
    {
        return $this->builder->where('last_call', 'like', '%' . $lastCall . '%');
    }

    public function practice($practice)
    {
        return $this->builder->where('practice', 'like', '%' . $practice . '%');
    }

    public function billing_provider($billingProvider) {
        return $this->builder->where('billing_provider', 'like', '%'. $billingProvider . '%');
    }


    public function sort_is_manual($term)
    {
        return $this->builder->orderBy('is_manual', $term);
    }

    public function sort_nurse($term)
    {
        return $this->builder->orderBy('nurse', $term);
    }

    public function sort_patient_id($term)
    {
        return $this->builder->orderBy('patient_id', $term);
    }

    public function sort_scheduled_date($term)
    {
        return $this->builder->orderBy('scheduled_date', $term);
    }

    public function sort_ccm_time($term)
    {
        return $this->builder->orderBy('ccm_time', $term);
    }

    public function sort_bhi_time($term)
    {
        return $this->builder->orderBy('bhi_time', $term);
    }

    public function sort_practice($term)
    {
        return $this->builder->orderBy('practice', $term);
    }

    public function sort_scheduler($term)
    {
        return $this->builder->orderBy('scheduler', $term);
    }

    public function sort_last_call($term)
    {
        return $this->builder->orderBy('last_call', $term);
    }

    public function globalFilters(): array
    {
        return [];
    }
}