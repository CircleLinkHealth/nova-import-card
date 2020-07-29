<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Filters;

use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EnrolleeFilters extends QueryFilters
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function attempt_count($count)
    {
        if (empty($count)) {
            return $this->builder;
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

    public function cell_phone($number)
    {
        if (empty($number)) {
            return $this->builder;
        }

        return $this->filterPhone('cell_phone', $number);
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

        $hideStatus = [
            Enrollee::ENROLLED,
            Enrollee::LEGACY,
        ];

        if (array_key_exists('hideStatus', $decoded) && is_array($decoded['hideStatus'])) {
            $hideStatus = array_merge($decoded['hideStatus'], $hideStatus);
        }

        //Default filtering will only be added here
        $decoded['hideStatus']    = $hideStatus;
        $decoded['attempt_count'] = '';

        return $decoded;
    }

    public function hideAssigned($hideAssigned)
    {
        if ($hideAssigned) {
            return $this->builder->where('care_ambassador_name', '=', null);
        }

        return $this->builder;
    }

    public function hideStatus($statuses)
    {
        return $this->builder->whereNotIn('status', $statuses);
    }

    public function home_phone($number)
    {
        if (empty($number)) {
            return $this->builder;
        }

        return $this->filterPhone('home_phone', $number);
    }

    public function id($id)
    {
        if (empty($id)) {
            return $this->builder;
        }

        return $this->builder->where('id', 'like', '%'.$id.'%');
    }

    public function invited($invited)
    {
        if (empty($invited)) {
            return $this->builder;
        }

        return $this->builder->where('invited', boolval($invited));
    }

    public function isCsv()
    {
        return array_key_exists('csv', $this->filters());
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

    public function other_phone($number)
    {
        if (empty($number)) {
            return $this->builder;
        }

        return $this->filterPhone('other_phone', $number);
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

    public function primary_phone($number)
    {
        if (empty($number)) {
            return $this->builder;
        }

        return $this->filterPhone('primary_phone', $number);
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

    public function status($status = null)
    {
        if (empty($status)) {
            return $this->builder;
        }

        //ca-director page sends multiple options as array
        if (is_array($status)) {
            $statuses = collect($status)->pluck('id')->toArray();

            return $this->builder->whereIn('status', $statuses);
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

    private function filterPhone($field, $number)
    {
        if (Str::contains($number, '-')) {
            $number = str_replace('-', '', $number);
        }

        return $this->builder->where($field, 'like', '%'.$number.'%');
    }
}
