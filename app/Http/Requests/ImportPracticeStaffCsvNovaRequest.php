<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Requests;

use CircleLinkHealth\Customer\Entities\Practice;
use Laravel\Nova\Http\Requests\NovaRequest;

class ImportPracticeStaffCsvNovaRequest extends NovaRequest
{
    private $practice;

    /**
     * @return mixed
     */
    public function getPractice()
    {
        return $this->practice;
    }

    public function newResource()
    {
        $resource = parent::newResource();

        //this way we "decorate" existing behavior and just adding ours
        //here we could stop if practice was not set for example
        if ($this->getPractice() && is_object($resource)) {
            $resource->practice = $this->getPractice();
        }

        return $resource;
    }

    /**
     * @param mixed $practice
     */
    public function setPractice(Practice $practice): ?Practice
    {
        //we only want to set this value once in the lifecycle of the request
        if ( ! $this->practice) {
            $this->practice = $practice;

            return $this->practice;
        }

        return null;
    }
}
