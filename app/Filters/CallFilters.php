<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Filters;

use CircleLinkHealth\CpmAdmin\Repositories\CallRepository;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\PatientContactWindow;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Request;

class CallFilters extends QueryFilters
{
    public function __construct(Request $request, CallRepository $callRepository)
    {
        parent::__construct($request);
    }

    /**
     * @param $noCallAttemptsSinceLastSuccess
     *
     * @throws \Exception
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function attemptsSinceLastSuccess($noCallAttemptsSinceLastSuccess)
    {
        if ( ! is_numeric($noCallAttemptsSinceLastSuccess)) {
            throw new \Exception('noCallAttemptsSinceLastSuccess must be a numeric value.');
        }

        return $this->builder
            ->whereHas('inboundUser.patientInfo', function ($q) use ($noCallAttemptsSinceLastSuccess) {
                $q->whereNoCallAttemptsSinceLastSuccess($noCallAttemptsSinceLastSuccess);
            });
    }

    public function billingProvider($term)
    {
        return $this->builder->whereHas('inboundUser.billingProvider.user', function ($q) use ($term) {
            $q->where('display_name', 'LIKE', "%${term}%");
        });
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
                $q->where('display_name', 'like', "%${term}%");
            });
    }

    public function globalFilters(): array
    {
        return [];
    }

    /**
     * Scope for calls by the date the patient was last called.
     *
     * @param $date
     *
     * @throws \Exception
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function lastCallDate($date)
    {
        validateYYYYMMDDDateString($date);

        return $this->builder
            ->whereHas('inboundUser.patientInfo', function ($q) use ($date) {
                $q->whereLastContactTime($date);
            });
    }

    public function minScheduledDate($date)
    {
        if ( ! array_key_exists('unassigned', $this->filters())) {
            return $this->builder
                ->where('scheduled_date', '>=', $date);
        }

        return $this->builder;
    }

    /**
     * Scope for nurse and patient, who may be any of inbound or outbound callers.
     *
     * @param mixed $term
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function nurse($term)
    {
        return $this->builder->where(function ($q) use ($term) {
            $q->whereHas('outboundUser.nurseInfo', function ($q) use ($term) {
                $q->whereHas('user', function ($q) use ($term) {
                    $q->where('display_name', 'like', "%${term}%");
                });
            })->orWhereHas('inboundUser.nurseInfo', function ($q) use ($term) {
                $q->whereHas('user', function ($q) use ($term) {
                    $q->where('display_name', 'like', "%${term}%");
                });
            });
        });
    }

    public function ofActivePractices()
    {
        return $this->builder->whereHas('inboundUser.primaryPractice', function ($q) {
            $q->where('active', true);
        });
    }

    public function patient($term)
    {
        return $this->builder->whereHas('outboundUser.patientInfo', function ($q) use ($term) {
            $q->whereHas('user', function ($q) use ($term) {
                $q->where('display_name', 'like', "%${term}%");
            });
        })->orWhereHas('inboundUser.patientInfo', function ($q) use ($term) {
            $q->whereHas('user', function ($q) use ($term) {
                $q->where('display_name', 'like', "%${term}%");
            });
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
                $q->where('display_name', 'like', "%${name}%");
            });
    }

    public function patientStatus($term)
    {
        return $this->builder->whereHas('outboundUser.patientInfo', function ($q) use ($term) {
            $q->where('ccm_status', 'LIKE', "${term}%");
        })->orWhereHas('inboundUser.patientInfo', function ($q) use ($term) {
            $q->where('ccm_status', 'LIKE', "${term}%");
        });
    }

    public function practice($term)
    {
        return $this->builder->whereHas('inboundUser.primaryPractice', function ($q) use ($term) {
            $q->where('display_name', 'LIKE', "%${term}%");
        });
    }

    /**
     * Scope for scheduled calls.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scheduled()
    {
        return $this->builder->scheduled();
    }

    /**
     * Scope for calls by scheduled date.
     *
     * @param $date
     *
     * @throws \Exception
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scheduledDate($date)
    {
        return $this->builder
            ->where('scheduled_date', 'LIKE', '%'.$date.'%');
    }

    public function scheduler($term)
    {
        return $this->builder->where('scheduler', 'LIKE', "%${term}%");
    }

    public function sort_bhiTime($term = null)
    {
        return $this->sort_by_patient_summaries_column('bhi_time', $term);
    }

    public function sort_callTimeEnd($term = 'asc')
    {
        return $this->builder->orderBy('window_end', $term);
    }

    public function sort_callTimeStart($term = 'asc')
    {
        return $this->builder->orderBy('window_start', $term);
    }

    public function sort_ccmTime($term = null)
    {
        return $this->sort_by_patient_summaries_column('ccm_time', $term);
    }

    public function sort_id($type = null)
    {
        if ('desc' == $type) {
            return $this->builder->orderByDesc('id');
        }

        return $this->builder->orderBy('id');
    }

    public function sort_lastCall($term = null)
    {
        return $this->builder
            ->with('inboundUser.patientInfo')
            ->join('calls_view', 'calls.inbound_cpm_id', '=', 'calls_view.patient_id')
            ->orderBy('calls_view.last_call', $term)
            ->groupBy('calls.inbound_cpm_id')
            ->select(['calls.*']);
    }

    public function sort_lastCallStatus($term = null)
    {
        $patientInfoTable = (new Patient())->getTable();

        return $this->builder
            ->with('inboundUser.patientInfo')
            ->join($patientInfoTable, 'calls.inbound_cpm_id', '=', "${patientInfoTable}.user_id")
            ->orderBy("${patientInfoTable}.no_call_attempts_since_last_success", $term)
            ->groupBy('calls.inbound_cpm_id')
            ->select(['calls.*']);
    }

    public function sort_no_of_successful_calls($term = null)
    {
        return $this->builder
            ->select('calls.*')
            ->with('inboundUser.patientSummaries')
            ->join(
                (new PatientMonthlySummary())->getTable(),
                'calls.inbound_cpm_id',
                '=',
                (new PatientMonthlySummary())->getTable().'.patient_id'
            )
            ->where(
                (new PatientMonthlySummary())->getTable().'.month_year',
                Carbon::now()->startOfMonth()->toDateString()
            )
            ->orderBy((new PatientMonthlySummary())->getTable().'.no_of_successful_calls', $term)
            ->groupBy('calls.inbound_cpm_id');
    }

    public function sort_nurse($term = null)
    {
        if ($this->builder->has('outboundUser.nurseInfo.user')) {
            return $this->builder
                ->select('calls.*')
                ->join('users', 'users.id', '=', 'calls.outbound_cpm_id')
                ->orderBy('users.display_name', $term);
        }

        return $this->builder
            ->select('calls.*')
            ->join('users', 'users.id', '=', 'calls.inbound_cpm_id')
            ->orderBy('users.display_name', $term);
    }

    public function sort_patient($term = null)
    {
        return $this->builder
            ->join('users', 'users.id', '=', 'calls.inbound_cpm_id')
            ->orderBy('users.display_name', $term)
            ->select(['calls.*']);
    }

    public function sort_patient_contact_windows($term = null)
    {
        $aggregate = 'asc' == $term
            ? 'asc'
            : 'desc';

        return $this->builder
            ->select(
                'calls.*',
                \DB::raw('group_concat(DISTINCT '.(new PatientContactWindow())->getTable().".day_of_week ORDER BY day_of_week ${aggregate} SEPARATOR ',') as sort_day")
            )
            ->with('inboundUser.patientInfo.contactWindows')
            ->join((new Patient())->getTable(), 'calls.inbound_cpm_id', '=', (new Patient())->getTable().'.user_id')
            ->join(
                (new PatientContactWindow())->getTable(),
                (new PatientContactWindow())->getTable().'.patient_info_id',
                '=',
                (new Patient())->getTable().'.id'
            )
            ->orderBy('sort_day', $term)
            ->groupBy('calls.inbound_cpm_id');
    }

    public function sort_patientId($term = null)
    {
        return $this->builder
            ->select('calls.*')
            ->join('users', 'users.id', '=', 'calls.inbound_cpm_id')
            ->orderBy('users.id', $term);
    }

    public function sort_patientStatus($term = null)
    {
        $patientInfoTable = (new Patient())->getTable();

        return $this->builder
            ->with('inboundUser.patientInfo')
            ->join($patientInfoTable, 'calls.inbound_cpm_id', '=', "${patientInfoTable}.user_id")
            ->orderBy("${patientInfoTable}.ccm_status", $term)
            ->groupBy('calls.inbound_cpm_id')
            ->select(['calls.*']);
    }

    public function sort_practice($term = null)
    {
        $practicesTable = (new Practice())->getTable();
        $usersTable     = (new User())->getTable();

        return $this->builder
            ->with('inboundUser.primaryPractice')
            ->join($usersTable, 'calls.inbound_cpm_id', '=', "${usersTable}.id")
            ->join($practicesTable, "${usersTable}.program_id", '=', "${practicesTable}.id")
            ->orderBy("${practicesTable}.display_name", $term)
            ->groupBy('calls.inbound_cpm_id')
            ->select(['calls.*']);
    }

    public function sort_preferredCallDays($term = null)
    {
        $aggregate = 'asc' == $term
            ? 'max'
            : 'min';

        return $this->builder->selectRaw('calls.*, '." ${aggregate}(".(new PatientContactWindow())->getTable().'.day_of_week) as sort_day')
            ->with('inboundUser.patientInfo.contactWindows')
            ->join(
                (new Patient())->getTable(),
                'calls.inbound_cpm_id',
                '=',
                (new Patient())->getTable().'.user_id'
            )
            ->join(
                (new PatientContactWindow())->getTable(),
                (new PatientContactWindow())->getTable().'.patient_info_id',
                '=',
                (new Patient())->getTable().'.id'
            )
            ->orderBy('sort_day', $term)
            ->groupBy('calls.inbound_cpm_id');
    }

    public function sort_scheduledDate($term = null)
    {
        return $this->builder->orderBy('scheduled_date', $term);
    }

    public function sort_scheduler($term = null)
    {
        return $this->builder->orderBy('scheduler', $term);
    }

    /**
     * calls with no nurse assigned.
     */
    public function unassigned()
    {
        return $this->builder
            ->where(function ($q) {
                $q->whereNull('outbound_cpm_id')
                    ->where(function ($q) {
                        $q->whereNull('scheduled_date')
                            ->orWhere('scheduled_date', '>=', Carbon::now()->startOfDay()->toDateString());
                    });
            });
    }

    private function sort_by_patient_summaries_column($column, $term)
    {
        $joinTable = (new PatientMonthlySummary())->getTable();
        $date      = Carbon::now()->startOfMonth();

        return $this->builder
            ->with([
                'inboundUser.patientSummaries' => function ($q) use ($date) {
                    return $q->where('month_year', '=', $date);
                },
            ])
            ->leftJoin($joinTable, function ($join) use ($joinTable, $date) {
                $join->on('calls.inbound_cpm_id', '=', "${joinTable}.patient_id")
                    ->where("${joinTable}.month_year", '=', $date);
            })
            ->orderBy("${joinTable}.${column}", $term)
            ->groupBy('calls.inbound_cpm_id')
            ->select(['calls.*']);
    }
}
