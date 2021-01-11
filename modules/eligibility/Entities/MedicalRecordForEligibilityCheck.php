<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Entities;

class MedicalRecordForEligibilityCheck
{
    /**
     * @var
     */
    private $address_line_1;

    /**
     * @var
     */
    private $address_line_2;

    /**
     * @var array
     */
    private $allergies = [];

    /**
     * @var
     */
    private $cell_phone;

    /**
     * @var
     */
    private $city;

    /**
     * @var
     */
    private $date_of_birth;

    /**
     * @var
     */
    private $first_name;

    /**
     * @var array
     */
    private $insurance_plans = [];

    /**
     * @var
     */
    private $last_name;

    /**
     * @var
     */
    private $last_visit;

    /**
     * @var array
     */
    private $medications = [];

    /**
     * @var
     */
    private $middle_name;
    /**
     * @var
     */
    private $patient_id;

    /**
     * @var
     */
    private $postal_code;

    /**
     * @var
     */
    private $preferred_provider;

    /**
     * @var
     */
    private $primary_phone;

    /**
     * @var array
     */
    private $problems = [];

    /**
     * @var
     */
    private $state;

    /**
     * @return mixed
     */
    public function getAddressLine1()
    {
        return $this->address_line_1;
    }

    /**
     * @return mixed
     */
    public function getAddressLine2()
    {
        return $this->address_line_2;
    }

    public function getAllergies(): array
    {
        return $this->allergies;
    }

    /**
     * @return mixed
     */
    public function getCellPhone()
    {
        return $this->cell_phone;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @return mixed
     */
    public function getDateOfBirth()
    {
        return $this->date_of_birth;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    public function getInsurancePlans(): array
    {
        return $this->insurance_plans;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * @return mixed
     */
    public function getLastVisit()
    {
        return $this->last_visit;
    }

    public function getMedications(): array
    {
        return $this->medications;
    }

    /**
     * @return mixed
     */
    public function getMiddleName()
    {
        return $this->middle_name;
    }

    /**
     * @return mixed
     */
    public function getPatientId()
    {
        return $this->patient_id;
    }

    /**
     * @return mixed
     */
    public function getPostalCode()
    {
        return $this->postal_code;
    }

    /**
     * @return mixed
     */
    public function getPreferredProvider()
    {
        return $this->preferred_provider;
    }

    /**
     * @return mixed
     */
    public function getPrimaryPhone()
    {
        return $this->primary_phone;
    }

    public function getProblems(): array
    {
        return $this->problems;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param mixed $address_line_1
     */
    public function setAddressLine1($address_line_1): void
    {
        $this->address_line_1 = $address_line_1;
    }

    /**
     * @param mixed $address_line_2
     */
    public function setAddressLine2($address_line_2): void
    {
        $this->address_line_2 = $address_line_2;
    }

    public function setAllergies(array $allergies): void
    {
        $this->allergies = $allergies;
    }

    /**
     * @param mixed $cell_phone
     */
    public function setCellPhone($cell_phone): void
    {
        $this->cell_phone = $cell_phone;
    }

    /**
     * @param mixed $city
     */
    public function setCity($city): void
    {
        $this->city = $city;
    }

    /**
     * @param mixed $date_of_birth
     */
    public function setDateOfBirth($date_of_birth): void
    {
        $this->date_of_birth = $date_of_birth;
    }

    /**
     * @param mixed $first_name
     */
    public function setFirstName($first_name): void
    {
        $this->first_name = $first_name;
    }

    public function setInsurancePlans(array $insurance_plans): void
    {
        $this->insurance_plans = $insurance_plans;
    }

    /**
     * @param mixed $last_name
     */
    public function setLastName($last_name): void
    {
        $this->last_name = $last_name;
    }

    /**
     * @param mixed $last_visit
     */
    public function setLastVisit($last_visit): void
    {
        $this->last_visit = $last_visit;
    }

    public function setMedications(array $medications): void
    {
        $this->medications = $medications;
    }

    /**
     * @param mixed $middle_name
     */
    public function setMiddleName($middle_name): void
    {
        $this->middle_name = $middle_name;
    }

    /**
     * @param mixed $patient_id
     */
    public function setPatientId($patient_id): void
    {
        $this->patient_id = $patient_id;
    }

    /**
     * @param mixed $postal_code
     */
    public function setPostalCode($postal_code): void
    {
        $this->postal_code = $postal_code;
    }

    /**
     * @param mixed $preferred_provider
     */
    public function setPreferredProvider($preferred_provider): void
    {
        $this->preferred_provider = $preferred_provider;
    }

    /**
     * @param mixed $primary_phone
     */
    public function setPrimaryPhone($primary_phone): void
    {
        $this->primary_phone = $primary_phone;
    }

    public function setProblems(array $problems): void
    {
        $this->problems = $problems;
    }

    /**
     * @param mixed $state
     */
    public function setState($state): void
    {
        $this->state = $state;
    }
}
