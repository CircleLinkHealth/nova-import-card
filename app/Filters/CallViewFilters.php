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
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function sort_nurse($term = null)
    {
        return $this->builder
            ->orderBy('nurse', $term);
    }

    public function sort_patientId($term = null)
    {
        return $this->builder
            ->orderBy('patient_id', $term);
    }

    public function sort_patient($term = null)
    {
        return $this->builder
            ->orderBy('patient', $term);
    }

    //todo: remove or fix
    public function sort_patient_contact_windows($term = null)
    {
        return $this->builder;
    }

    public function sort_no_of_successful_calls($term = null)
    {
        return $this->builder
            ->orderBy('no_of_successful_calls', $term);
    }

    public function sort_scheduledDate($term = null)
    {
        return $this->builder
            ->orderBy('scheduled_date', $term);
    }

    public function sort_patientStatus($term = null)
    {
        return $this->builder
            ->orderBy('patient_status', $term);
    }

    public function sort_practice($term = null)
    {
        return $this->builder
            ->orderBy('practice', $term);
    }

    public function sort_scheduler($term = null)
    {
        return $this->builder
            ->orderBy('scheduler', $term);
    }

    public function sort_callTimeStart($term = 'asc')
    {
        return $this->builder
            ->orderBy('call_time_start', $term);
    }

    public function sort_callTimeEnd($term = 'asc')
    {
        return $this->builder
            ->orderBy('call_time_end', $term);
    }

    public function sort_lastCall($term = null)
    {
        return $this->builder
            ->orderBy('last_call', $term);
    }

    public function sort_lastCallStatus($term = null)
    {
        return $this->builder
            ->orderBy('last_call_status', $term);
    }

    public function sort_ccmTime($term = null)
    {
        return $this->builder
            ->orderBy('ccm_time', $term);
    }

    public function sort_bhiTime($term = null)
    {
        return $this->builder
            ->orderBy('bhi_time', $term);
    }

    public function sort_preferredCallDays($term = null)
    {
        return $this->builder
            ->orderBy('preferred_call_days', $term);
    }

    public function globalFilters(): array
    {
        return [];
    }
}