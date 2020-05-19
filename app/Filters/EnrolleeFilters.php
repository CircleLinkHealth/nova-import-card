<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Filters;

use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Http\Request;

class EnrolleeFilters extends QueryFilters
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function attempt_count($count)
    {
        if (empty($count)) {
            return $this->builder->where(function ($subQuery) {
                $subQuery->where('attempt_count', '<', Enrollee::MAX_CALL_ATTEMPTS)
                    ->orWhere('attempt_count', null);
            });
        }

        return $this->builder->where('attempt_count', '=', $count);
    }

    public function auto_enrollment_triggered($aet)
    {
        if (empty($aet) && '0' !== $aet) {
            return $this->builder;
        }

        return $this->builder->where('auto_enrollment_triggered', boolval($aet));
    }

    public function care_ambassador_name($name)
    {
        if (empty($name)) {
            return $this->builder;
        }

        return $this->builder->where('care_ambassador_name', 'like', '%'.$name.'%');
    }

    public function eligibility_job_id($id)
    {
        if (empty($id)) {
            return $this->builder;
        }

        return $this->builder->where('eligibility_job_id', 'like', '%'.$id.'%');
    }

    public function enrollment_non_responsive($enr)
    {
        if (empty($enr) && '0' !== $enr) {
            return $this->builder;
        }

        return $this->builder->where('enrollment_non_responsive', boolval($enr));
    }

    public function first_name($name)
    {
        if (empty($name)) {
            return $this->builder;
        }

        return $this->builder->where('first_name', 'like', '%'.$name.'%');
    }

    public function globalFilters(): array
    {
        $query = $this->request->get('query');

        $decoded = json_decode($query, true);
        //We're using this filter class for both CA-Director panel and Enrollment/Enrollee-List,
        //so let's just say globally hide Enrolled and Legacy only, then anything extra comes from each vue-component
        $decoded['hideStatus'] = array_merge($decoded['hideStatus'], [
            Enrollee::ENROLLED,
            Enrollee::LEGACY,
        ]);
        $decoded['attempt_count'] = '';

        return $decoded;
    }

    public function hideAssigned($hideAssigned)
    {
        if ($hideAssigned) {
            return $this->builder->where('care_ambassador_name', '=', null);
        }

        return $this->builder->where('care_ambassador_name', '!=', null);
    }

    public function hideStatus($statuses)
    {
        return $this->builder->whereNotIn('status', $statuses);
    }

    public function id($id)
    {
        if (empty($id)) {
            return $this->builder;
        }

        return $this->builder->where('id', 'like', '%'.$id.'%');
    }

    public function isolateUploadedViaCsv($isolate)
    {
        if ($isolate) {
            return $this->builder->whereIn('source', [Enrollee::UPLOADED_CSV]);
        }

        return $this->builder;
    }

    public function lang($lang)
    {
        if (empty($lang)) {
            return $this->builder;
        }

        return $this->builder->where('lang', 'like', '%'.$lang.'%');
    }

    public function last_attempt_at($dateString)
    {
        if (empty($date)) {
            return $this->builder;
        }

        return $this->builder->where('last_attempt_at', 'like', '%'.$dateString.'%');
    }

    public function last_name($name)
    {
        if (empty($name)) {
            return $this->builder;
        }

        return $this->builder->where('last_name', 'like', '%'.$name.'%');
    }

    public function medical_record_id($id)
    {
        if (empty($id)) {
            return $this->builder;
        }

        return $this->builder->where('medical_record_id', 'like', '%'.$id.'%');
    }

    public function mrn($id)
    {
        if (empty($id)) {
            return $this->builder;
        }

        return $this->builder->where('mrn', 'like', '%'.$id.'%');
    }

    public function practice_name($name)
    {
        if (empty($name)) {
            return $this->builder;
        }

        return $this->builder->where('practice_name', 'like', '%'.$name.'%');
    }

    public function primary_insurance($id)
    {
        if (empty($id)) {
            return $this->builder;
        }

        return $this->builder->where('primary_insurance', 'like', '%'.$id.'%');
    }

    public function provider_name($name)
    {
        if (empty($name)) {
            return $this->builder;
        }

        return $this->builder->where('provider_name', 'like', '%'.$name.'%');
    }

    public function requested_callback($dateString)
    {
        if (empty($date)) {
            return $this->builder;
        }

        return $this->builder->where('requested_callback', 'like', '%'.$dateString.'%');
    }

    public function secondary_insurance($insurance)
    {
        if (empty($insurance)) {
            return $this->builder;
        }

        return $this->builder->where('secondary_insurance', 'like', '%'.$insurance.'%');
    }

    public function source($source)
    {
        if (empty($source)) {
            return $this->builder;
        }

        return $this->builder->where('source', 'like', '%'.$source.'%');
    }

    public function status($status)
    {
        if (empty($status)) {
            return $this->builder;
        }

        return $this->builder->where('status', 'like', '%'.$status.'%');
    }

    public function tertiary_insurance($insurance)
    {
        if (empty($insurance)) {
            return $this->builder;
        }

        return $this->builder->where('tertiary_insurance', 'like', '%'.$insurance.'%');
    }

    public function user_id($id)
    {
        if (empty($id)) {
            return $this->builder;
        }

        return $this->builder->where('user_id', 'like', '%'.$id.'%');
    }
}
