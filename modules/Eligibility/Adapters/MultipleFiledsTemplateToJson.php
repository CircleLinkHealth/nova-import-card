<?php


namespace CircleLinkHealth\Eligibility\Adapters;


use CircleLinkHealth\Eligibility\MedicalRecordImporter\Loggers\NumberedAllergyFields;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Loggers\NumberedMedicationFields;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Loggers\NumberedProblemFields;

class MultipleFiledsTemplateToJson
{
    public static function fromRow(array $row) {
        if (count(preg_grep('/^problem_[\d]*/', array_keys($row))) > 0) {
            $problems = (new NumberedProblemFields())->handle($row);
        
            $row['problems_string'] = json_encode(
                [
                    'Problems' => $problems,
                ]
            );
        }
    
        if (count(preg_grep('/^medication_[\d]*/', array_keys($row))) > 0) {
            $medications = (new NumberedMedicationFields())->handle($row);
        
            $row['medications_string'] = json_encode(
                [
                    'Medications' => $medications,
                ]
            );
        }
    
        if (count(preg_grep('/^allergy_[\d]*/', array_keys($row))) > 0) {
            $allergies = (new NumberedAllergyFields())->handle($row);
        
            $row['allergies_string'] = json_encode(
                [
                    'Allergies' => $allergies,
                ]
            );
        }
    
        return $row;
    }
}