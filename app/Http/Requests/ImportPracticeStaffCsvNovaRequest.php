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
    
    public function authorize()
    {
        //write logic to ensure user has access to do this
        return true;
    }
    
    /**
     * @return mixed
     */
    private function getPracticeFromRequest() : ?Practice
    {
        if ($this->practice) {
            return $this->practice;
        }
    
        $this->practice = Practice::findOrFail($this->input('practice_id'));
        
        return $this->practice;
    }
    
    /**
     * @return array
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            //add more validation
            'practice_id' => 'required'
        ]);
    }
    
    public function newResource()
    {
        $resource = parent::newResource();

        //this way we "decorate" existing behavior and just adding ours
        //here we could stop if practice was not set for example
        if ($this->getPracticeFromRequest() && is_object($resource)) {
            $resource->practice = $this->getPracticeFromRequest();
        }

        return $resource;
    }
}
