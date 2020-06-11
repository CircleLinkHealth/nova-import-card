<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Filters;

use CircleLinkHealth\Customer\Entities\Role;
use Illuminate\Http\Request;

class CallViewFilters extends QueryFilters
{
    /**
     * CallViewFilters constructor.
     * Sorting and filters just work, simply because
     * the column names match the view table names and they
     * are ordered by them.
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function billing_provider($billingProvider)
    {
        return $this->builder->where('billing_provider', 'like', '%'.$billingProvider.'%');
    }

    public function completed_tasks()
    {
        return $this->builder
            ->where('type', '!=', 'call')
            ->where(function ($q) {
                $q->where('status', '=', 'done')
                    ->orWhere('status', '=', 'reached');
            });
    }

    public function globalFilters(): array
    {
        return ['software_only_user' => ! auth()->user()->isAdmin()];
    }

    public function last_call($lastCall)
    {
        return $this->builder->where('last_call', 'like', '%'.$lastCall.'%');
    }

    public function nurse($nurse)
    {
        return $this->builder->where('nurse', 'like', '%'.$nurse.'%');
    }

    public function patient($name)
    {
        return $this->builder->where('patient', 'like', '%'.$name.'%');
    }

    public function patient_id($id)
    {
        return $this->builder->where('patient_id', '=', $id);
    }

    public function practice($practice)
    {
        return $this->builder->where('practice', 'like', '%'.$practice.'%');
    }

    public function scheduled()
    {
        return $this->builder->where('status', '=', 'scheduled');
    }

    public function scheduled_date($date)
    {
        return $this->builder->where('scheduled_date', 'like', '%'.$date.'%');
    }

    public function software_only_user($value)
    {
        if ( ! $value) {
            return $this->builder;
        }
        $roleIds = Role::getIdsFromNames(['software-only']);
        $user    = auth()->user();

        return $this->builder->whereRaw(
            'practice_id IN (SELECT program_id FROM practice_role_user WHERE role_id IN (?) AND user_id = ?)',
            [implode(',', $roleIds), $user->id]
        );
    }

    public function sort_bhi_time($term)
    {
        return $this->builder->orderBy('bhi_time', $term);
    }

    public function sort_ccm_time($term)
    {
        return $this->builder->orderBy('ccm_time', $term);
    }

    public function sort_is_manual($term)
    {
        return $this->builder->orderBy('is_manual', $term);
    }

    public function sort_last_call($term)
    {
        return $this->builder->orderBy('last_call', $term);
    }

    public function sort_nurse($term)
    {
        return $this->builder->orderBy('nurse', $term);
    }

    public function sort_patient_id($term)
    {
        return $this->builder->orderBy('patient_id', $term);
    }

    public function sort_practice($term)
    {
        return $this->builder->orderBy('practice', $term);
    }

    public function sort_scheduled_date($term)
    {
        return $this->builder->orderBy('scheduled_date', $term);
    }

    public function sort_scheduler($term)
    {
        return $this->builder->orderBy('scheduler', $term);
    }

    public function sort_state($term)
    {
        return $this->builder->orderBy('state', $term);
    }

    public function state($state)
    {
        return $this->builder->where('state', 'like', '%'.$state.'%');
    }

    public function type($type)
    {
        return $this->builder->where('type', 'like', '%'.$type.'%');
    }

    public function unassigned()
    {
        return $this->builder->whereNull('nurse_id');
    }
}
