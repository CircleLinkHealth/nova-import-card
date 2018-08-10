<?php

namespace App\Filters;

use App\CarePerson;
use App\CarePlan;
use App\Patient;
use App\Practice;
use App\Repositories\PatientReadRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PatientFilters extends QueryFilters
{
    protected $request;

    public function __construct(Request $request, PatientReadRepository $patientRepository)
    {
        $this->request = $request;
        parent::__construct($request);
    }

    public function name($term)
    {
        return $this->builder->where('users.display_name', 'LIKE', "%$term%");
    }

    public function provider($provider)
    {
        return $this->builder->whereHas('billingProvider', function ($query) use ($provider) {
            $query->whereHas('user', function ($q) use ($provider) {
                $q->where('display_name', $provider)->orWhere('id', $provider);
            });
        });
    }

    public function practice($id)
    {
        return $this->builder->whereHas('primaryPractice', function ($query) use ($id) {
            $query->where('id', $id);
        });
    }

    public function careplanStatus($status)
    {
        return $this->builder->whereHas('carePlan', function ($query) use ($status) {
            $query->where('status', $status)->orWhere('status', 'LIKE', '%\"status\":\"' . $status . '\"%');
        });
    }

    public function ccmStatus($status)
    {
        return $this->builder->whereHas('patientInfo', function ($query) use ($status) {
            $query->where('ccm_status', $status);
        });
    }

    public function dob($date)
    {
        return $this->builder->whereHas('patientInfo', function ($query) use ($date) {
            $query->where('birth_date', 'LIKE', '%' . $date . '%');
        });
    }

    public function phone($phone)
    {
        return $this->builder->whereHas('phoneNumbers', function ($query) use ($phone) {
            $query->where('number', 'LIKE', '%' . $phone . '%');
        });
    }

    public function age($age)
    {
        $year = Carbon::now()->subYear($age)->format('Y');

        return $this->builder->whereHas('patientInfo', function ($query) use ($year) {
            $query->where('birth_date', '>=', "$year-01-01")->where('birth_date', '<=', "$year-12-31");
        });
    }

    public function registeredOn($on)
    {
        return $this->builder->where('users.created_at', 'LIKE', '%' . $on . '%');
    }

    public function lastReading($reading)
    {
        return $this->builder->whereHas('lastObservation', function ($query) use ($reading) {
            return $query->where('obs_date', 'LIKE', $reading . '%');
        });
    }

    public function sort_name($type = null)
    {
        return $this->builder->orderBy('users.display_name', $type);
    }

    public function sort_provider($type = null)
    {
        $careTeamTable = (new CarePerson())->getTable();

        return $this->builder
            ->select('users.*')
            ->with('billingProvider.user')
            ->join($careTeamTable, 'users.id', '=', "$careTeamTable.user_id")
            ->where("$careTeamTable.type", CarePerson::BILLING_PROVIDER)
            ->join('users as providers', 'providers.id', '=', "$careTeamTable.member_user_id")
            ->orderBy("providers.display_name", $type);
    }

    public function sort_practice($type = null)
    {
        $practicesTable = (new Practice())->getTable();

        return $this->builder
            ->select('users.*')
            ->with('primaryPractice')
            ->join($practicesTable, "users.program_id", '=', "$practicesTable.id")
            ->orderBy("$practicesTable.display_name", $type)
            ->groupBy('users.id');
    }

    public function sort_ccmStatus($type = null)
    {
        $patientTable = (new Patient())->getTable();

        return $this->builder->select('users.*')->with('patientInfo')->join($patientTable, 'users.id', '=',
            "$patientTable.user_id")->orderBy("$patientTable.ccm_status", $type)->groupBy('users.id');
    }

    public function sort_careplanStatus($type = null)
    {
        $careplanTable = (new CarePlan())->getTable();

        return $this->builder->select('users.*')->with('carePlan')->join($careplanTable, 'users.id', '=',
            "$careplanTable.user_id")->orderBy("$careplanTable.status", $type)->groupBy('users.id');
    }

    public function sort_dob($type = null)
    {
        $patientTable = (new Patient())->getTable();

        return $this->builder->select('users.*')->with('patientInfo')->join($patientTable, 'users.id', '=',
            "$patientTable.user_id")->orderBy("$patientTable.birth_date", $type)->groupBy('users.id');
    }

    public function sort_age($type = null)
    {
        return $this->sort_dob(( ! $type || $type == 'asc')
            ? 'desc'
            : 'asc');
    }

    public function sort_registeredOn($type = null)
    {
        return $this->builder->orderBy('users.created_at', $type);
    }

    public function sort_ccm($type = null)
    {
        $patientTable = (new Patient())->getTable();

        return $this->builder->select('users.*')->with('patientInfo')->join($patientTable, 'users.id', '=',
            "$patientTable.user_id")
                             ->orderBy("$patientTable.cur_month_activity_time", $type)->groupBy('users.id');
    }

    public function excel()
    {
        return true;
    }

    public function isExcel()
    {
        return isset($this->filters()['excel']);
    }

    public function csv()
    {
        return true;
    }

    public function isCsv()
    {
        return isset($this->filters()['csv']);
    }

    public function autocomplete()
    {
        return $this->builder->select(['users.id', 'users.display_name', 'users.program_id']);
    }

    public function isAutocomplete()
    {
        return isset($this->filters()['autocomplete']);
    }

    public function ccmStatusDate($date)
    {
        return $this->builder->whereHas('patientInfo', function ($query) use ($date) {
            $query->where('date_paused', 'LIKE', "%$date%")
                  ->orWhere('date_withdrawn', 'LIKE', "%$date%")
                  ->orWhere('date_unreachable', 'LIKE', "%$date%");
        });
    }

    public function sort_ccmStatusDate($type = null)
    {
        $patientTable = (new Patient())->getTable();
        return $this->builder->select('users.*')
                             ->with('patientInfo')->join($patientTable, 'users.id', '=', "$patientTable.user_id")
                             ->orderBy("$patientTable.date_paused", $type)
                             ->orderBy("$patientTable.date_withdrawn", $type)
                             ->orderBy("$patientTable.date_unreachable", $type)
                             ->groupBy('users.id');

    }

    public function globalFilters(): array
    {
        return [];
    }
}