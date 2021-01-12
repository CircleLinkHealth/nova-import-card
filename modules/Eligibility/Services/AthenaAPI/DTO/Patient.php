<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Services\AthenaAPI\DTO;

use Carbon\Carbon;

class Patient
{
    protected $address1;

    protected $address2;

    protected $city;

    protected $departmentId;

    protected $dob;

    protected $doNotCall = false;

    protected $email;

    protected $firstName;

    protected $gender;

    protected $homePhone;

    protected $lastName;

    protected $mobilePhone;
    protected $practiceId;

    protected $state;

    protected $zip;

    public function __construct()
    {
    }

    public function getAddress1()
    {
        return $this->address1;
    }

    public function getAddress2()
    {
        return $this->address2;
    }

    public function getCity()
    {
        return $this->city;
    }

    /**
     * @return mixed
     */
    public function getDepartmentId()
    {
        return $this->departmentId;
    }

    /**
     * @return mixed
     */
    public function getDob()
    {
        return is_a($this->dob, Carbon::class)
            ? $this->dob->format('m/d/Y')
            : false;
    }

    /**
     * @return mixed
     */
    public function getDoNotCall()
    {
        return $this->doNotCall;
    }

    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return mixed
     */
    public function getGender()
    {
        return $this->gender;
    }

    public function getHomePhone()
    {
        return $this->homePhone;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    public function getMobilePhone()
    {
        return $this->mobilePhone;
    }

    /**
     * @return mixed
     */
    public function getPracticeId()
    {
        return $this->practiceId;
    }

    public function getState()
    {
        return $this->state;
    }

    public function getZip()
    {
        return $this->zip;
    }

    /**
     * @param null $address1
     */
    public function setAddress1($address1)
    {
        $this->address1 = $address1;
    }

    /**
     * @param null $address2
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;
    }

    /**
     * @param null $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @param mixed $departmentId
     */
    public function setDepartmentId($departmentId)
    {
        $this->departmentId = $departmentId;
    }

    /**
     * @param mixed $dob
     */
    public function setDob(Carbon $dob)
    {
        $this->dob = $dob;
    }

    /**
     * @param mixed $doNotCall
     */
    public function setDoNotCall(bool $doNotCall)
    {
        $this->doNotCall = $doNotCall;
    }

    /**
     * @param null $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @param mixed $gender
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    /**
     * @param null $homePhone
     */
    public function setHomePhone($homePhone)
    {
        $this->homePhone = $homePhone;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @param null $mobilePhone
     */
    public function setMobilePhone($mobilePhone)
    {
        $this->mobilePhone = $mobilePhone;
    }

    /**
     * @param mixed $practiceId
     */
    public function setPracticeId($practiceId)
    {
        $this->practiceId = $practiceId;
    }

    /**
     * @param null $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @param null $zip
     */
    public function setZip($zip)
    {
        $this->zip = $zip;
    }
}
