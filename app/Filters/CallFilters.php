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
        else {
            return $this->builder->orderByJoin('inboundUser.display_name', $term);
        }
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