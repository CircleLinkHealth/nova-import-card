<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Entities;

use Illuminate\Contracts\Support\Arrayable;

class Problem implements Arrayable
{
    private $code;
    private $code_system_name;
    private $end;
    private $name;
    private $problem_code_system_id;
    private $start;
    private $status;

    public static function create($attributes = [])
    {
        $entity = new static();

        foreach ($attributes as $key => $value) {
            $entity->{$key} = $value;
        }

        return $entity;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        //return name if code is empty, just in case code was put into name field
        return empty($this->code)
            ? $this->name
            : $this->code;
    }

    /**
     * @return mixed
     */
    public function getCodeSystemName()
    {
        return $this->code_system_name;
    }

    /**
     * @return mixed
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getProblemCodeSystemId()
    {
        return $this->problem_code_system_id;
    }

    /**
     * @return mixed
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $code
     *
     * @return Problem
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @param mixed $code_system_name
     *
     * @return Problem
     */
    public function setCodeSystemName($code_system_name)
    {
        $this->code_system_name = $code_system_name;

        return $this;
    }

    /**
     * @param mixed $end
     *
     * @return Problem
     */
    public function setEnd($end)
    {
        $this->end = $end;

        return $this;
    }

    /**
     * @param mixed $name
     *
     * @return Problem
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param mixed $problem_code_system_id
     *
     * @return Problem
     */
    public function setProblemCodeSystemId($problem_code_system_id)
    {
        $this->problem_code_system_id = $problem_code_system_id;

        return $this;
    }

    /**
     * @param mixed $start
     *
     * @return Problem
     */
    public function setStart($start)
    {
        $this->start = $start;

        return $this;
    }

    /**
     * @param mixed $status
     *
     * @return Problem
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'name'                   => $this->getName(),
            'code'                   => $this->getCode(),
            'code_system_name'       => $this->getStatus(),
            'problem_code_system_id' => $this->getProblemCodeSystemId(),
            'start'                  => $this->getStart(),
            'end'                    => $this->getEnd(),
            'status'                 => $this->getStatus(),
        ];
    }
}
