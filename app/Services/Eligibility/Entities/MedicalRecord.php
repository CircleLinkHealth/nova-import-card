<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 9/11/18
 * Time: 3:43 PM
 */

namespace App\Services\Eligibility\Entities;

class MedicalRecord
{
    /**
     * @var
     */
    private $patient_id;

    /**
     * @var
     */
    private $last_name;

    /**
     * @var
     */
    private $first_name;

    /**
     * @var
     */
    private $middle_name;

    /**
     * @var
     */
    private $date_of_birth;

    /**
     * @var
     */
    private $address_line_1;

    /**
     * @var
     */
    private $address_line_2;

    /**
     * @var
     */
    private $city;

    /**
     * @var
     */
    private $state;

    /**
     * @var
     */
    private $postal_code;

    /**
     * @var
     */
    private $primary_phone;

    /**
     * @var
     */
    private $cell_phone;

    /**
     * @var
     */
    private $preferred_provider;

    /**
     * @var
     */
    private $last_visit;

    /**
     * @var array
     */
    private $insurance_plans = [];

    /**
     * @var array
     */
    private $problems = [];

    /**
     * @var array
     */
    private $medications = [];

    /**
     * @var array
     */
    private $allergies = [];

    /**
     * @return array
     */
    public function getAllergies(): array
    {
        return $this->allergies;
    }

    /**
     * @param array $allergies
     */
    public function setAllergies(array $allergies): void
    {
        $this->allergies = $allergies;
    }

    /**
     * @return array
     */
    public function getMedications(): array
    {
        return $this->medications;
    }

    /**
     * @param array $medications
     */
    public function setMedications(array $medications): void
    {
        $this->medications = $medications;
    }

    /**
     * @return array
     */
    public function getProblems(): array
    {
        return $this->problems;
    }

    /**
     * @param array $problems
     */
    public function setProblems(array $problems): void
    {
        $this->problems = $problems;
    }

    /**
     * @return array
     */
    public function getInsurancePlans(): array
    {
        return $this->insurance_plans;
    }

    /**
     * @param array $insurance_plans
     */
    public function setInsurancePlans(array $insurance_plans): void
    {
        $this->insurance_plans = $insurance_plans;
    }

    /**
     * @return mixed
     */
    public function getLastVisit()
    {
        return $this->last_visit;
    }

    /**
     * @param mixed $last_visit
     */
    public function setLastVisit($last_visit): void
    {
        $this->last_visit = $last_visit;
    }

    /**
     * @return mixed
     */
    public function getPreferredProvider()
    {
        return $this->preferred_provider;
    }

    /**
     * @param mixed $preferred_provider
     */
    public function setPreferredProvider($preferred_provider): void
    {
        $this->preferred_provider = $preferred_provider;
    }

    /**
     * @return mixed
     */
    public function getCellPhone()
    {
        return $this->cell_phone;
    }

    /**
     * @param mixed $cell_phone
     */
    public function setCellPhone($cell_phone): void
    {
        $this->cell_phone = $cell_phone;
    }

    /**
     * @return mixed
     */
    public function getPrimaryPhone()
    {
        return $this->primary_phone;
    }

    /**
     * @param mixed $primary_phone
     */
    public function setPrimaryPhone($primary_phone): void
    {
        $this->primary_phone = $primary_phone;
    }

    /**
     * @return mixed
     */
    public function getPostalCode()
    {
        return $this->postal_code;
    }

    /**
     * @param mixed $postal_code
     */
    public function setPostalCode($postal_code): void
    {
        $this->postal_code = $postal_code;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param mixed $state
     */
    public function setState($state): void
    {
        $this->state = $state;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $city
     */
    public function setCity($city): void
    {
        $this->city = $city;
    }

    /**
     * @return mixed
     */
    public function getAddressLine2()
    {
        return $this->address_line_2;
    }

    /**
     * @param mixed $address_line_2
     */
    public function setAddressLine2($address_line_2): void
    {
        $this->address_line_2 = $address_line_2;
    }

    /**
     * @return mixed
     */
    public function getAddressLine1()
    {
        return $this->address_line_1;
    }

    /**
     * @param mixed $address_line_1
     */
    public function setAddressLine1($address_line_1): void
    {
        $this->address_line_1 = $address_line_1;
    }

    /**
     * @return mixed
     */
    public function getDateOfBirth()
    {
        return $this->date_of_birth;
    }

    /**
     * @param mixed $date_of_birth
     */
    public function setDateOfBirth($date_of_birth): void
    {
        $this->date_of_birth = $date_of_birth;
    }

    /**
     * @return mixed
     */
    public function getMiddleName()
    {
        return $this->middle_name;
    }

    /**
     * @param mixed $middle_name
     */
    public function setMiddleName($middle_name): void
    {
        $this->middle_name = $middle_name;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * @param mixed $first_name
     */
    public function setFirstName($first_name): void
    {
        $this->first_name = $first_name;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * @param mixed $last_name
     */
    public function setLastName($last_name): void
    {
        $this->last_name = $last_name;
    }

    /**
     * @return mixed
     */
    public function getPatientId()
    {
        return $this->patient_id;
    }

    /**
     * @param mixed $patient_id
     */
    public function setPatientId($patient_id): void
    {
        $this->patient_id = $patient_id;
    }
}
