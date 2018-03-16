<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 01/17/2018
 * Time: 12:44 AM
 */

namespace App\Filters;

use App\Patient;
use App\PatientContactWindow;
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
    * Scope for nurse and patient, who may be any of inbound or outbound callers
    *
    * @return \Illuminate\Database\Eloquent\Builder
    */
    public function nurse($term)
    {
        return $this->builder->whereHas('outboundUser.nurseInfo', function ($q) use ($term) {
            $q->whereHas('user', function ($q) use ($term) {
                $q->where('display_name', 'like', "%$term%");
            });
        })->orWhereHas('inboundUser.nurseInfo', function ($q) use ($term) {
            $q->whereHas('user', function ($q) use ($term) {
                $q->where('display_name', 'like', "%$term%");
            });
        });
    }

    public function patient($term) {
        return $this->builder->whereHas('outboundUser.patientInfo', function ($q) use ($term) {
            $q->whereHas('user', function ($q) use ($term) {
                $q->where('display_name', 'like', "%$term%");
            });
        })->orWhereHas('inboundUser.patientInfo', function ($q) use ($term) {
            $q->whereHas('user', function ($q) use ($term) {
                $q->where('display_name', 'like', "%$term%");
            });
        });
    }
    
    public function patientStatus($term) {
        return $this->builder->whereHas('outboundUser.patientInfo', function ($q) use ($term) {
            $q->where('ccm_status', 'LIKE', "$term%");
        })->orWhereHas('inboundUser.patientInfo', function ($q) use ($term) {
            $q->where('ccm_status', 'LIKE', "$term%");
        });
    }

    public function practice($term) {
        return $this->builder->whereHas('inboundUser.primaryPractice', function ($q) use ($term) {
            $q->where('display_name', 'LIKE', "%$term%");
        });
    }
    
    public function billingProvider($term) {
        return $this->builder->whereHas('inboundUser.billingProvider.user', function ($q) use ($term) {
            $q->where('display_name', 'LIKE', "%$term%");
        });
    }
    
    public function scheduler($term) {
        return $this->builder->where('scheduler', 'LIKE', "%$term%");
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
        //validateYYYYMMDDDateString($date);

        return $this->builder
            ->where('scheduled_date', 'LIKE', '%'.$date.'%');
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

    /**
     * @param $noCallAttemptsSinceLastSuccess
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     * @throws \Exception
     */
    public function attemptsSinceLastSuccess($noCallAttemptsSinceLastSuccess) {
        if (!is_numeric($noCallAttemptsSinceLastSuccess)) {
            throw new \Exception("noCallAttemptsSinceLastSuccess must be a numeric value.");
        }

        return $this->builder
            ->whereHas('inboundUser.patientInfo', function ($q) use ($noCallAttemptsSinceLastSuccess) {
                $q->whereNoCallAttemptsSinceLastSuccess($noCallAttemptsSinceLastSuccess);
            });
    }
    
    public function sort_nurse($term = null) {
        if ($this->builder->has('outboundUser.nurseInfo.user')) {
            return $this->builder->orderByJoin('outboundUser.display_name', $term);
        }
        return $this->builder->orderByJoin('inboundUser.display_name', $term);
    }
    
    public function sort_patientId($term = null) {
        return $this->builder->join('users', 'users.id', 'calls.' . ('inbound_cpm_id'))->orderBy('users.id', $term);
    }
    
    public function sort_patient($term = null) {
        return $this->builder->orderByJoin('inboundUser.display_name', $term);
    }
    
    public function sort_scheduledDate($term = null) {
        return $this->builder->orderBy('scheduled_date', $term);
    }
    
    public function sort_patientStatus($term = null) {
        return $this->builder->orderByJoin('inboundUser.patientInfo.ccm_status', $term);
    }
    
    public function sort_practice($term = null) {
        return $this->builder->orderByJoin('inboundUser.primaryPractice.display_name', $term);
    }
    
    public function sort_scheduler($term = null) {
        return $this->builder->orderBy('scheduler', $term);
    }
    
    public function sort_callTimeStart($term = 'asc') {
        return $this->builder->orderBy('window_start', $term);
    }
    
    public function sort_callTimeEnd($term = 'asc') {
        return $this->builder->orderBy('window_end', $term);
    }
    
    public function sort_lastCall($term = null) {
        return $this->builder->orderByJoin('inboundUser.patientInfo.last_contact_time', $term);
    }
    
    public function sort_lastCallStatus($term = null) {
        return $this->builder->orderByJoin('inboundUser.patientInfo.last_call_status', $term);
    }
    
    public function sort_ccmTime($term = null) {
        return $this->builder->orderByJoin('inboundUser.patientInfo.cur_month_activity_time', $term);
    }

    public function sort_preferredCallDays($term = null) {
        return $this->builder->selectRaw('calls.*, '." $term(".(new PatientContactWindow)->getTable() . '.day_of_week) as sort_day')
                ->with('inboundUser.patientInfo.contactWindows')
                ->join((new Patient)->getTable(), 'calls.inbound_cpm_id', '=', (new Patient)->getTable() . '.user_id')
                ->join((new PatientContactWindow)->getTable(), (new PatientContactWindow)->getTable() . '.patient_info_id', '=', (new Patient)->getTable() . '.id')
                ->orderBy('sort_day', $term)
                ->groupBy('calls.inbound_cpm_id');
    }
    
    public function sort_id($type = null) {
        if ($type == 'desc') {
            return $this->builder->orderByDesc('id');
        }
        return $this->builder->orderBy('id');
    }

    public function globalFilters(): array
    {
        return [];
    }
}