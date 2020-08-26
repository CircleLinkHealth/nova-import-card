<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Filters;

use App\User;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\CarePerson;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use Illuminate\Http\Request;

class PatientFilters extends QueryFilters
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        parent::__construct($request);
    }

    public function age($age)
    {
        $year = Carbon::now()->subYear($age)->format('Y');

        return $this->builder->whereHas('patientInfo', function ($query) use ($year) {
            $query->where('birth_date', '>=', "${year}-01-01")->where('birth_date', '<=', "${year}-12-31");
        });
    }

    public function autocomplete()
    {
        return $this->builder->select(['users.id', 'users.display_name', 'users.program_id']);
    }

    public function careplanStatus($status)
    {
        return $this->builder->whereHas('carePlan', function ($query) use ($status) {
            $query->where(function ($q) use ($status) {
                if ( ! is_array($status)) {
                    $status = [$status];
                }
                foreach ($status as $val) {
                    $q->orWhere('status', 'LIKE', '%'.$val.'%');
                }
            });
        });
    }

    public function ccmStatus($status)
    {
        return $this->builder->whereHas('patientInfo', function ($query) use ($status) {
            $query->where('ccm_status', $status);
        });
    }

    public function ccmStatusDate($date)
    {
        return $this->builder->whereHas('patientInfo', function ($query) use ($date) {
            $query->where(function ($subQuery) use ($date) {
                $subQuery->where('ccm_status', Patient::PAUSED)
                    ->where('date_paused', 'LIKE', "%${date}%");
            })
                ->orWhere(function ($subQuery) use ($date) {
                    $subQuery->where('ccm_status', Patient::UNREACHABLE)
                        ->where('date_unreachable', 'LIKE', "%${date}%");
                })
                ->orWhere(
                    function ($subQuery) use ($date) {
                        $subQuery->where('ccm_status', Patient::WITHDRAWN)
                            ->where('date_withdrawn', 'LIKE', "%${date}%");
                    }
                );
        });
    }

    public function csv()
    {
        return true;
    }

    public function dob($date)
    {
        return $this->builder->whereHas('patientInfo', function ($query) use ($date) {
            $query->where('birth_date', 'LIKE', '%'.$date.'%');
        });
    }

    public function excel()
    {
        return true;
    }

    public function globalFilters(): array
    {
        $filters = [];

        $currFilters = $this->request->all();
        if ( ! isset($currFilters['careplanStatus'])) {
            /** @var User $user */
            $user = auth()->user();
            if ( ! $user->isAdmin()) {
                // CPM-1790, non-admins should only see rn_approved, and provider_approved
                $filters['careplanStatus'] = [CarePlan::PROVIDER_APPROVED, CarePlan::RN_APPROVED];
            }
        }

        return $filters;
    }

    public function isAutocomplete()
    {
        return isset($this->filters()['autocomplete']);
    }

    public function isCsv()
    {
        return array_key_exists('csv', $this->filters());
    }

    public function isExcel()
    {
        return array_key_exists('excel', $this->filters());
    }

    public function lastReading($reading)
    {
        return $this->builder->whereHas('lastObservation', function ($query) use ($reading) {
            return $query->where('obs_date', 'LIKE', $reading.'%');
        });
    }

    public function location($location)
    {
        return $this->builder->whereHas('patientInfo.location', function ($q) use ($location) {
            $q->where('name', $location)->orWhere('id', $location);
        });
    }

    public function mrn($mrn)
    {
        return $this->builder->whereHas('patientInfo', function ($query) use ($mrn) {
            $query->where('mrn_number', 'LIKE', '%'.$mrn.'%');
        });
    }

    public function name($term)
    {
        return $this->builder->where('users.display_name', 'LIKE', "%${term}%");
    }

    public function phone($phone)
    {
        return $this->builder->whereHas('phoneNumbers', function ($query) use ($phone) {
            $query->where('number', 'LIKE', '%'.$phone.'%');
        });
    }

    public function practice($id)
    {
        return $this->builder->whereHas('primaryPractice', function ($query) use ($id) {
            $query->where('id', $id);
        });
    }

    public function provider($provider)
    {
        return $this->builder->whereHas('billingProvider', function ($query) use ($provider) {
            $query->whereHas('user', function ($q) use ($provider) {
                $q->where('display_name', $provider)->orWhere('id', $provider);
            });
        });
    }

    public function registeredOn($on)
    {
        return $this->builder->where('users.created_at', 'LIKE', '%'.$on.'%');
    }

    public function sort_age($type = null)
    {
        return $this->sort_dob(( ! $type || 'asc' == $type)
            ? 'desc'
            : 'asc');
    }

    public function sort_bhi($type = null)
    {
        $joinTable = (new PatientMonthlySummary())->getTable();
        $date      = Carbon::now()->startOfMonth();

        return $this->builder
            ->select('users.*')
            ->with([
                'patientSummaries' => function ($q) use ($date) {
                    return $q->where('month_year', '=', $date);
                },
            ])
            ->leftJoin($joinTable, function ($join) use ($joinTable, $date) {
                $join->on('users.id', '=', "${joinTable}.patient_id")
                    ->where("${joinTable}.month_year", '=', $date);
            })
            ->orderBy("${joinTable}.bhi_time", $type)
            ->groupBy('users.id');
    }

    public function sort_careplanStatus($type = null)
    {
        $careplanTable = (new CarePlan())->getTable();

        return $this->builder->select('users.*')->with('carePlan')->join(
            $careplanTable,
            'users.id',
            '=',
            "${careplanTable}.user_id"
        )->orderBy("${careplanTable}.status", $type)->groupBy('users.id');
    }

    public function sort_ccm($type = null)
    {
        $joinTable = (new PatientMonthlySummary())->getTable();
        $date      = Carbon::now()->startOfMonth();

        return $this->builder
            ->select('users.*')
            ->with([
                'patientSummaries' => function ($q) use ($date) {
                    return $q->where('month_year', '=', $date);
                },
            ])
            ->leftJoin($joinTable, function ($join) use ($joinTable, $date) {
                $join->on('users.id', '=', "${joinTable}.patient_id")
                    ->where("${joinTable}.month_year", '=', $date);
            })
            ->orderBy("${joinTable}.ccm_time", $type)
            ->groupBy('users.id');
    }

    public function sort_ccmStatus($type = null)
    {
        $patientTable = (new Patient())->getTable();

        return $this->builder->select('users.*')->with('patientInfo')->join(
            $patientTable,
            'users.id',
            '=',
            "${patientTable}.user_id"
        )->orderBy("${patientTable}.ccm_status", $type)->groupBy('users.id');
    }

    public function sort_ccmStatusDate($type = null)
    {
        $patientTable = (new Patient())->getTable();

        return $this->builder->select('users.*')
            ->with('patientInfo')->join($patientTable, 'users.id', '=', "${patientTable}.user_id")
            ->orderBy("${patientTable}.date_paused", $type)
            ->orderBy("${patientTable}.date_withdrawn", $type)
            ->orderBy("${patientTable}.date_unreachable", $type)
            ->groupBy('users.id');
    }

    public function sort_dob($type = null)
    {
        $patientTable = (new Patient())->getTable();

        return $this->builder->select('users.*')->with('patientInfo')->join(
            $patientTable,
            'users.id',
            '=',
            "${patientTable}.user_id"
        )->orderBy("${patientTable}.birth_date", $type)->groupBy('users.id');
    }

    public function sort_mrn($type = null)
    {
        $patientTable = (new Patient())->getTable();

        return $this->builder->select('users.*')->with('patientInfo')->join(
            $patientTable,
            'users.id',
            '=',
            "${patientTable}.user_id"
        )->orderBy("${patientTable}.mrn_number", $type)->groupBy('users.id');
    }

    public function sort_name($type = null)
    {
        return $this->builder->orderBy('users.display_name', $type);
    }

    public function sort_practice($type = null)
    {
        $practicesTable = (new Practice())->getTable();

        return $this->builder
            ->select('users.*')
            ->with('primaryPractice')
            ->join($practicesTable, 'users.program_id', '=', "${practicesTable}.id")
            ->orderBy("${practicesTable}.display_name", $type)
            ->groupBy('users.id');
    }

    public function sort_provider($type = null)
    {
        $careTeamTable = (new CarePerson())->getTable();

        return $this->builder
            ->select('users.*')
            ->with('billingProvider.user')
            ->join($careTeamTable, 'users.id', '=', "${careTeamTable}.user_id")
            ->where("${careTeamTable}.type", CarePerson::BILLING_PROVIDER)
            ->join('users as providers', 'providers.id', '=', "${careTeamTable}.member_user_id")
            ->orderBy('providers.display_name', $type);
    }

    public function sort_registeredOn($type = null)
    {
        return $this->builder->orderBy('users.created_at', $type);
    }

    public function sort_withdrawnReason($type = null)
    {
        $patientTable = (new Patient())->getTable();

        return $this->builder->select('users.*')->with('patientInfo')->join(
            $patientTable,
            'users.id',
            '=',
            "${patientTable}.user_id"
        )->orderBy("${patientTable}.withdrawn_reason", $type)->groupBy('users.id');
    }

    public function withdrawnReason($reason)
    {
        return $this->builder->whereHas('patientInfo', function ($query) use ($reason) {
            $query->where('withdrawn_reason', 'LIKE', '%'.$reason.'%');
        });
    }
}
