<?php
/**
 * Created by IntelliJ IDEA.
 * User: pangratioscosma
 * Date: 28/09/2018
 * Time: 13:16
 */

namespace App;

class WT1CsvParser
{

    /**
     * @var
     */
    private $patients;

    /**
     * WT1CsvParser constructor.
     */
    public function __construct()
    {
        $this->patients = [];
    }

    public function parseFile(String $fileName)
    {
        $arr = parseCsvToArray($fileName);
        $this->parseCsvArray($arr);
    }

    public function parseCsvArray($arr)
    {
        foreach ($arr as $row) {
            $this->addToResult($row);
        }
    }

    public function toArray()
    {
        return $this->patients;
    }

    public function toJson()
    {
        return json_encode($this->patients);
    }

    /**
     * Parse a CSV row and add to result of patients.
     * NOTE: using plain PHP instead of Eloquent for better performance.
     *
     * @param $row array
     */
    private function addToResult($row)
    {
        $patientId = $row['patient_id'];
        if (! isset($this->patients[$patientId])) {
            $this->patients[$patientId] = [];
        }

        $entry = $this->patients[$patientId];

        if (! isset($entry['insurance_plans'])) {
            $entry['insurance_plans'] = []; //we want this to be translated to { "primary" : {}, "secondary": {} }
        }

        if (! isset($entry['problems'])) {
            $entry['problems'] = []; //we want this to be translated to [{}]
        }

        if (! isset($entry['medications'])) {
            $entry['medications'] = []; //we want this to be translated to [{}]
        }

        if (! isset($entry['allergies'])) {
            $entry['allergies'] = []; //we want this to be translated to [{}]
        }

        $entry['patient_id'] = $patientId;

        $entry['mrn'] = $this->getValue($row, 'mrn');

        $entry['last_name']      = $this->getValue($row, 'lastname');
        $entry['first_name']     = $this->getValue($row, 'firstname');
        $entry['middle_name']    = $this->getValue($row, 'middlename');
        $entry['date_of_birth']  = $this->getValue($row, 'dob');
        $entry['address_line_1'] = $this->getValue($row, 'addr1', '');
        $entry['address_line_2'] = $this->getValue($row, 'addr2', '');
        $entry['city']           = $this->getValue($row, 'city', '');
        $entry['state']          = $this->getValue($row, 'state', '');
        $entry['postal_code']    = $this->getValue($row, 'zip', '');
        $entry['primary_phone']  = $this->getValue($row, 'phonehome', '');
        $entry['cell_phone']     = $this->getValue($row, 'phonecell', '');

        $entry['last_visit'] = $this->getValue($row, 'dos');

        $entry['preferred_provider'] = $this->getProviderValue($row);

        $entry['insurance_plans'] = $this->getInsurances($row);

        $entry['problems'] = $this->getMergedList($row, $entry['problems'], 'reported', function ($value) {
            return [
                'code'       => null,
                'name'       => $value,
                'code_type'  => null,
                'start_date' => null,
            ];
        });

        $entry['allergies'] = $this->getMergedList($row, $entry['allergies'], 'allergy', function ($value) {
            return [
                'name' => $value,
            ];
        });

        $entry['medications'] = $this->getMergedList($row, $entry['medications'], 'meds', function ($value) {
            return [
                'name'       => $value,
                'sig'        => null,
                'start_date' => null,
            ];
        });

        $this->patients[$patientId] = $entry;
    }

    private function getInsurances($row)
    {
        $result    = [];
        $primary   = $this->getPrimaryInsurance($row);
        $secondary = $this->getSecondaryInsurance($row);

        if ($primary) {
            $result['primary'] = $primary;
        }
        if ($secondary) {
            $result['secondary'] = $secondary;
        }
        return $result;
    }

    private function getPrimaryInsurance($row)
    {

        /**
         * "plan"           => "Test Medicare",
         * "group_number"   => "",
         * "policy_number"  => "123455",
         * "insurance_type" => "Medicare",
         */

        $plan = $this->getValue($row, 'primaryins');
        if (! $plan) {
            return null;
        }

        $result                   = [];
        $result["plan"]           = $plan;
        $result["policy_number"]  = $this->getValue($row, 'primaryinspol');
        $result["group_number"]   = null;
        $result["insurance_type"] = "Medicare";
        return $result;
    }

    private function getSecondaryInsurance($row)
    {
        $plan = $this->getValue($row, 'secondaryins');
        if (! $plan) {
            return null;
        }

        $result                  = [];
        $result["plan"]          = $plan;
        $result["policy_number"] = $this->getValue($row, 'secondaryinspol');
        return $result;
    }

    private function getMergedList($row, $currentList, $fieldName, $mapper)
    {
        $rowList = $this->getListFromFields($row, $fieldName);

        foreach ($rowList as $fieldValue) {
            $found = false;
            foreach ($currentList as $entry) {
                if (strcasecmp($entry['name'], $fieldValue) === 0) {
                    $found = true;
                    break;
                }
            }

            if (! $found) {
                $currentList[] = $mapper($fieldValue);
            }
        }
        return $currentList;
    }

    private function getListFromFields($row, $fieldName)
    {
        $lookingFor = $fieldName;
        $result     = [];
        foreach ($row as $key => $value) {
            $isFieldFound = strcasecmp(substr($key, 0, strlen($lookingFor)), $lookingFor) === 0;
            if (! $isFieldFound) {
                continue;
            }

            $fieldValue = str_replace($lookingFor, "", $key);
            //ignore {FieldName}None
            if (strcasecmp($fieldValue, 'none') === 0) {
                continue;
            }

            //ignore MedsNoMeds
            if (strcasecmp($key, 'MedsNoMeds') === 0) {
                continue;
            }

            if ($this->getBoolValue($row, $key)) {
                $result[] = $fieldValue;
            }
        }
        return $result;
    }

    private function getProviderValue($row, $default = null)
    {
        $firstName  = $this->getValue($row, 'providerfirstname', '');
        $middleName = $this->getValue($row, 'providermiddlename', '');
        $lastName   = $this->getValue($row, 'providerlastname', '');
        $result     = '';
        if (! empty($firstName)) {
            $result .= $firstName;
            if (! empty($middleName)) {
                $result .= ' ';
            }
        }
        if (! empty($middleName)) {
            $result .= $middleName;
            if (! empty($lastName)) {
                $result .= ' ';
            }
        }
        if (! empty($lastName)) {
            $result .= $lastName;
        }

        if (empty($result)) {
            $result = $default;
        }
        return $result;
    }

    private function getValue($row, $key, $default = null)
    {
        if (! isset($row[$key])) {
            return $default;
        }

        if (empty($row[$key])) {
            return $default;
        }

        if ($row[$key] === "null" || $row[$key] === "NULL" || $row[$key] === "\\N") {
            return $default;
        }
        return $row[$key];
    }

    /**
     * Returns true if value is Y, false if N.
     * All other fields return false.
     *
     * @param $row
     * @param $key
     * @param bool $default
     *
     * @return bool|null
     */
    private function getBoolValue($row, $key, $default = false)
    {
        $val = $this->getValue($row, $key, $default);
        if ($val === false) {
            return $val;
        }
        return $val === 'Y';
    }
}
