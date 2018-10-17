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
        if ( ! isset($this->patients[$patientId])) {
            $this->patients[$patientId] = [];
        }

        //MedicalRecordNumber, Encounter_ID, Org_ID, Provider_ID, ProviderFirstName, ProviderLastName

        $entry = $this->patients[$patientId];

        //exit if problem already processed
        if (isset($entry['problems'])) {
            $problems   = $entry['problems'];
            $rowProblem = $row['description'];
            foreach ($problems as $problem) {
                if ($problem['name'] === $rowProblem) {
                    return;
                }
            }
        }

        if ( ! isset($entry['insurance_plans'])) {
            $entry['insurance_plans'] = []; //we want this to be translated to { "primary" : {}, "secondary": {} }
        }

        if ( ! isset($entry['problems'])) {
            $entry['problems'] = []; //we want this to be translated to [{}]
        }

        if ( ! isset($entry['medications'])) {
            $entry['medications'] = []; //we want this to be translated to [{}]
        }

        if ( ! isset($entry['allergies'])) {
            $entry['allergies'] = []; //we want this to be translated to [{}]
        }

        $entry['patient_id']     = $patientId;
        $entry['mrn']            = $this->getValue($row, 'medicalrecordnumber');
        $entry['last_name']      = $this->getValue($row, 'lastname');
        $entry['first_name']     = $this->getValue($row, 'firstname');
        $entry['middle_name']    = null;
        $entry['date_of_birth']  = $this->getValue($row, 'dob');
        $entry['address_line_1'] = $this->getValue($row, 'addr1');
        $entry['address_line_2'] = null;
        $entry['city']           = $this->getValue($row, 'city');
        $entry['state']          = $this->getValue($row, 'state');
        $entry['postal_code']    = $this->getValue($row, 'zip');
        $entry['primary_phone']  = $this->getValue($row, 'phonehome');
        $entry['cell_phone']     = $this->getValue($row, 'phonecell');
        //datecreated was in first csv
        $entry['last_visit'] = $this->getValue($row, 'encounterdate') ?? $this->getValue($row, 'datecreated');


        if (isset($row['providerfirstname'])) {
            $entry['preferred_provider'] = $row['providerfirstname'] . ' ' . ($row['providerlastname'] ?? '');
        } else {
            $entry['preferred_provider'] = null;
        }

        $entry['insurance_plans']['primary'] = [
            "plan" => "Medicare",
        ];

//        $entry['insurance_plans']['primary']   = [
//            "plan"           => "Test Insurance",
//            "group_number"   => "",
//            "policy_number"  => "TEST1234",
//            "insurance_type" => "Medicaid",
//        ];
//        $entry['insurance_plans']['secondary'] = [
//            "plan"           => "Test Medicare",
//            "group_number"   => "",
//            "policy_number"  => "123455",
//            "insurance_type" => "Medicare",
//        ];

        $entry['problems'][] = [
            "name" => $row['description'],
        ];

//        $entry['problems'][]    = [
//            "name"       => "Chronic Obstructive Pulmonary Disease",
//            "code_type"  => "ICD9",
//            "code"       => "496",
//            "start_date" => "07-30-2013",
//        ];

//        $entry['medications'][] = [
//            "name"       => "Avinza 30 mg oral capsule, ER multiphase 24 hr",
//            "sig"        => "take 1 capsule by oral route daily for 30 days",
//            "start_date" => "2014-03-11",
//        ];

        if (isset($row['answer'])) {
            $entry['medications'][] = [
                "name" => $row['answer'],
            ];
        }

//        $entry['allergies'][] = [
//            "name" => "Animal Dander",
//        ];

        $this->patients[$patientId] = $entry;
    }

    private function getValue($row, $key)
    {
        if ( ! isset($row[$key])) {
            return null;
        }

        if (empty($row[$key])) {
            return null;
        }

        if ($row[$key] === "null" || $row[$key] === "NULL") {
            return null;
        }
        return $row[$key];
    }


}