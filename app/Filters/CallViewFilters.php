<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Filters;

use App\Call;
use CircleLinkHealth\Core\Constants;
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

    public function billing_provider($billingProvider = null)
    {
        if ( ! $billingProvider) {
            return $this->builder;
        }

        return $this->builder->where('billing_provider', 'like', '%'.$billingProvider.'%');
    }

    public function callbacks_admin()
    {
        return $this->type('Call Back');
    }

    public function completed_tasks()
    {
        return $this->builder
            ->where('type', '!=', 'call')
            ->where(function ($q) {
                $q->where('status', '=', Call::DONE)
                    ->orWhere('status', '=', Call::REACHED);
            });
    }

    public function demo($value = null)
    {
        if (is_null($value)) {
            return $this->builder;
        }

        return $this->builder->where('is_demo', '=', $value);
    }

    public function globalFilters(): array
    {
        $filters = [];

        if ( ! auth()->user()->isAdmin()) {
            $filters[] = 'non_admin_user';
        }

        if (auth()->user()->isCallbacksAdmin()) {
            $filters[] = 'callbacks_admin';
        }

        return $filters;
    }

    public function last_call($lastCall = null)
    {
        if ( ! $lastCall) {
            return $this->builder;
        }

        return $this->builder->where('last_call', 'like', '%'.$lastCall.'%');
    }

    public function non_admin_user()
    {
        return $this->builder
            ->whereIn('practice_id', function ($q) {
                $q->select('program_id')
                    ->from('practice_role_user')
                    ->where('user_id', auth()->id())
                    ->whereIn('role_id', function ($q) {
                        $q->select('lv_roles.id')
                            ->from('lv_roles')
                            ->join('permissibles', function ($join) {
                                $join->on('permissibles.permissible_id', '=', 'lv_roles.id')
                                    ->where('permissibles.permissible_type', '=', Role::class);
                            })
                            ->join('lv_permissions', function ($join) {
                                $join->on('permissibles.permission_id', '=', 'lv_permissions.id')
                                    ->where('lv_permissions.name', Constants::PERM_CAN_VIEW_PATIENT_ACTIVITY_MANAGEMENT_PAGE);
                            });
                    });
            });
    }

    public function nurse($nurse = null)
    {
        if ( ! $nurse) {
            return $this->builder;
        }

        return $this->builder->where('nurse', 'like', '%'.$nurse.'%');
    }

    public function patient($name = null)
    {
        if ( ! $name) {
            return $this->builder;
        }

        return $this->builder->where('patient', 'like', '%'.$name.'%');
    }

    public function patient_id($id = null)
    {
        if ( ! $id) {
            return $this->builder;
        }

        return $this->builder->where('patient_id', '=', $id);
    }

    public function practice($practice = null)
    {
        if ( ! $practice) {
            return $this->builder;
        }

        return $this->builder->where('practice', 'like', '%'.$practice.'%');
    }

    public function preferred_contact_language($value = null)
    {
        if ( ! $value) {
            return $this->builder;
        }

        return $this->builder->where('preferred_contact_language', '=', $value);
    }

    public function scheduled()
    {
        return $this->builder->where('status', '=', 'scheduled');
    }

    public function scheduled_date($date = null)
    {
        if ( ! $date) {
            return $this->builder;
        }

        return $this->builder->where('scheduled_date', 'like', '%'.$date.'%');
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

    public function sort_preferred_contact_language($term)
    {
        return $this->builder->orderBy('preferred_contact_language', $term);
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

    public function state($state = null)
    {
        if ( ! $state) {
            return $this->builder;
        }

        return $this->builder->where('state', 'like', '%'.$state.'%');
    }

    public function type($type = null)
    {
        if ( ! $type) {
            return $this->builder;
        }

        return $this->builder->where('type', 'like', '%'.$type.'%');
    }

    public function unassigned()
    {
        return $this->builder->whereNull('nurse_id');
    }
}
