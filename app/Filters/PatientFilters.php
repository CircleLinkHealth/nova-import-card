<?php

namespace App\Filters;

use App\Repositories\PatientReadRepository;
use Illuminate\Http\Request;

class PatientFilters extends QueryFilters
{
    protected $request;

    public function __construct(Request $request, PatientReadRepository $patientRepository)
    {
        $this->request = $request;
        parent::__construct($request);
    }

    public function name($term) {
        return $this->builder->where('display_name', 'LIKE', "%$term%");
    }

    public function provider($provider) {
        return $this->builder->whereHas('billingProvider', function ($query) use ($provider) {
            $query->whereHas('user', function ($q) use ($provider) {
                $q->where('display_name', $provider)->orWhere('id', $provider);
            });
        });
    }
    
    public function program($program) {
        return $this->builder->whereHas('primaryPractice', function ($query) use ($program) {
            $query->where('display_name', $program);
        });
    }
    
    public function careplanStatus($status) {
        return $this->builder->whereHas('carePlan', function ($query) use ($status) {
            $query->where('status', $status)->orWhere('status', 'LIKE', '%\"status\":\"' . $status . '\"%');
        });
    }
    
    public function ccmStatus($status) {
        return $this->builder->whereHas('patientInfo', function ($query) use ($status) {
            $query->where('ccm_status', $status);
        });
    }
    
    public function dob($date) {
        return $this->builder->whereHas('patientInfo', function ($query) use ($date) {
            $query->where('birth_date', 'LIKE', '%' . $date . '%');
        });
    }
    
    public function phone($phone) {
        return $this->builder->whereHas('phoneNumbers', function ($query) use ($phone) {
            $query->where('number', 'LIKE', '%' . $phone . '%');
        });
    }
    
    public function age($age) {
        $date = Carbon::now()->subYear($age + 1)->format('Y');
        return $this->builder->whereHas('patientInfo', function ($query) use ($date) {
           $query->where('birth_date', 'LIKE', $date . '%');
        });
    }
    
    public function registeredOn($on) {
        return $this->builder->where('created_at', 'LIKE', '%' . $on . '%');
    }
    
    public function lastReading($reading) {
        return $this->builder->whereHas('lastObservation', function ($query) use ($reading) {
            return $query->where('obs_date', 'LIKE', $reading . '%');
        });
    }

    public function globalFilters(): array
    {
        return [];
    }
}