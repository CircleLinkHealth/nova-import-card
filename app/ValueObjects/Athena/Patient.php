<?php
/**
 * Created by PhpStorm.
 * User: kakoushias
 * Date: 20/12/2017
 * Time: 5:45 PM
 */

namespace App\ValueObjects\Athena;


use Carbon\Carbon;

class Patient
{
    protected $practiceId;

    protected $departmentId;

    protected $dob;

    protected $firstName;

    protected $lastName;

    protected $address = null;

    protected $city = null;

    protected $email = null;

    protected $homePhone = null;

    protected $mobilePhone = null;

    protected $state = null;

    protected $zip = null;


    public function __construct()
    {

    }


    /**
     * @return mixed
     */
    public function getPracticeId()
    {
        return $this->practiceId;
    }

    /**
     * @param mixed $practiceId
     */
    public function setPracticeId($practiceId)
    {
        $this->practiceId = $practiceId;
    }



    /**
     * @return mixed
     */
    public function getDepartmentId()
    {
        return $this->departmentId;
    }

    /**
     * @param mixed $departmentId
     */
    public function setDepartmentId($departmentId)
    {
        $this->departmentId = $departmentId;
    }

    /**
     * @return mixed
     */
    public function getDob()
    {
        return $this->dob->toDateString() ?? false;
    }

    /**
     * @param mixed $dob
     */
    public function setDob(Carbon $dob)
    {
        $this->dob = $dob;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return null
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param null $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return null
     */
    public function getMobilePhone()
    {
        return $this->mobilePhone;
    }

    /**
     * @param null $mobilePhone
     */
    public function setMobilePhone($mobilePhone)
    {
        $this->mobilePhone = $mobilePhone;
    }

    /**
     * @return null
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param null $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return null
     */
    public function getHomePhone()
    {
        return $this->homePhone;
    }

    /**
     * @param null $homePhone
     */
    public function setHomePhone($homePhone)
    {
        $this->homePhone = $homePhone;
    }

    /**
     * @return null
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param null $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param null $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return null
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * @param null $zip
     */
    public function setZip($zip)
    {
        $this->zip = $zip;
    }


}