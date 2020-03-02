<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\SqlViews\BaseSqlView;

class PcmEligiblePatients extends BaseSqlView
{
    /**
     * Create the sql view.
     */
    public function createSqlView(): bool
    {
        return \DB::statement(
            "
        CREATE VIEW {$this->getViewName()}
        AS
        SELECT REPLACE(REPLACE(
            JSON_UNQUOTE(JSON_EXTRACT(`eligibility_jobs`.`data`,'$.chargeable_services_codes_and_problems.G2065')),'[',''),']','') AS `G2065_problem_id`,
            JSON_UNQUOTE(JSON_EXTRACT(`eligibility_jobs`.`data`,'$.department_name')) AS `location`,
            JSON_UNQUOTE(JSON_EXTRACT(`eligibility_jobs`.`data`,'$.referring_provider_name')) AS `provider`,
            JSON_UNQUOTE(JSON_EXTRACT(`eligibility_jobs`.`data`,'$.primary_insurance')) AS `primary_insurance`,
            JSON_UNQUOTE(JSON_EXTRACT(`eligibility_jobs`.`data`,'$.secondary_insurance')) AS `secondary_insurance`,JSON_UNQUOTE(JSON_EXTRACT(`eligibility_jobs`.`data`,'$.tertiary_insurance')) AS `tertiary_insurance`,JSON_UNQUOTE(JSON_EXTRACT(`eligibility_jobs`.`data`,'$.last_encounter')) AS `last_encounter`,JSON_UNQUOTE(JSON_EXTRACT(`eligibility_jobs`.`data`,'$.home_phone')) AS `home_phone`,JSON_UNQUOTE(JSON_EXTRACT(`eligibility_jobs`.`data`,'$.cell_phone')) AS `cell_phone`,JSON_UNQUOTE(JSON_EXTRACT(`eligibility_jobs`.`data`,'$.dob')) AS `dob`,JSON_UNQUOTE(JSON_EXTRACT(`eligibility_jobs`.`data`,'$.language')) AS `lang`,JSON_UNQUOTE(JSON_EXTRACT(`eligibility_jobs`.`data`,'$.mrn_number')) AS `mrn`,JSON_UNQUOTE(JSON_EXTRACT(`eligibility_jobs`.`data`,'$.first_name')) AS `first_name`,JSON_UNQUOTE(JSON_EXTRACT(`eligibility_jobs`.`data`,'$.last_name')) AS `last_name`,JSON_UNQUOTE(JSON_EXTRACT(`eligibility_jobs`.`data`,'$.street')) AS `address`,JSON_UNQUOTE(JSON_EXTRACT(`eligibility_jobs`.`data`,'$.street2')) AS `address_2`,JSON_UNQUOTE(JSON_EXTRACT(`eligibility_jobs`.`data`,'$.city')) AS `city`,JSON_UNQUOTE(JSON_EXTRACT(`eligibility_jobs`.`data`,'$.state')) AS `state`,JSON_UNQUOTE(JSON_EXTRACT(`eligibility_jobs`.`data`,'$.zip')) AS `zip`,JSON_UNQUOTE(JSON_EXTRACT(`eligibility_jobs`.`data`,'$.primary_phone')) AS `primary_phone`,JSON_UNQUOTE(JSON_EXTRACT(`eligibility_jobs`.`data`,'$.other_phone')) AS `other_phone`,JSON_UNQUOTE(JSON_EXTRACT(`eligibility_jobs`.`data`,'$.email')) AS `email`,`target_patients`.`user_id` AS `cpm_patient_id`,`target_patients`.`ehr_patient_id` AS `athenahealth_patient_id`,`eligibility_jobs`.`id` AS `eligibility_check_id`,JSON_UNQUOTE(JSON_EXTRACT(`eligibility_jobs`.`data`,'$.medical_record_type')) AS `medical_record_type`,JSON_UNQUOTE(JSON_EXTRACT(`eligibility_jobs`.`data`,'$.medical_record_id')) AS `medical_record_id` from (`eligibility_jobs` join `target_patients` on((`target_patients`.`eligibility_job_id` = `eligibility_jobs`.`id`))) where (`eligibility_jobs`.`batch_id` in (select `eligibility_batches`.`id` from `eligibility_batches` where (`eligibility_batches`.`practice_id` = 232)) and (JSON_UNQUOTE(JSON_EXTRACT(`eligibility_jobs`.`data`,'$.chargeable_services_codes_and_problems.G2065')) is not null))
      "
        );
    }
    
    /**
     * Get the name of the sql view.
     */
    public function getViewName(): string
    {
        return 'pcm_eligible_patients';
    }
}
