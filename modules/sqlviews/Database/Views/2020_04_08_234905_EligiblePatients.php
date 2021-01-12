<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\SqlViews\BaseSqlView;

class EligiblePatients extends BaseSqlView
{
    /**
     * Create the sql view.
     */
    public function createSqlView(): bool
    {
        return \DB::statement("
        CREATE VIEW {$this->getViewName()}
        AS
        SELECT
        eligibility_jobs.id as eligibility_job_id,
        eligibility_batches.practice_id as practice_id,
        JSON_UNQUOTE(JSON_EXTRACT(data, \"$.referring_provider_name\")) AS provider,
        JSON_UNQUOTE(JSON_EXTRACT(data, \"$.primary_insurance\")) AS primary_insurance,
        JSON_UNQUOTE(JSON_EXTRACT(data, \"$.secondary_insurance\")) AS secondary_insurance,
        JSON_UNQUOTE(JSON_EXTRACT(data, \"$.tertiary_insurance\")) AS tertiary_insurance,
        JSON_UNQUOTE(JSON_EXTRACT(data, \"$.last_encounter\")) AS last_encounter,
        JSON_UNQUOTE(JSON_EXTRACT(data, \"$.home_phone\")) AS home_phone,
        JSON_UNQUOTE(JSON_EXTRACT(data, \"$.cell_phone\")) AS cell_phone,
        JSON_UNQUOTE(JSON_EXTRACT(data, \"$.dob\")) AS dob,
        JSON_UNQUOTE(JSON_EXTRACT(data, \"$.language\")) AS lang,
        JSON_UNQUOTE(JSON_EXTRACT(data, \"$.mrn\")) AS mrn,
        JSON_UNQUOTE(JSON_EXTRACT(data, \"$.first_name\")) AS first_name,
        JSON_UNQUOTE(JSON_EXTRACT(data, \"$.last_name\")) AS last_name,
        JSON_UNQUOTE(JSON_EXTRACT(data, \"$.street\")) AS address,
        JSON_UNQUOTE(JSON_EXTRACT(data, \"$.street2\")) AS address_2,
        JSON_UNQUOTE(JSON_EXTRACT(data, \"$.city\")) AS city,
        JSON_UNQUOTE(JSON_EXTRACT(data, \"$.state\")) AS state,
        JSON_UNQUOTE(JSON_EXTRACT(data, \"$.zip\")) AS zip,
        JSON_UNQUOTE(JSON_EXTRACT(data, \"$.primary_phone\")) AS primary_phone,
        JSON_UNQUOTE(JSON_EXTRACT(data, \"$.other_phone\")) AS other_phone,
        JSON_UNQUOTE(JSON_EXTRACT(data, \"$.email\")) AS email,
        JSON_UNQUOTE(JSON_EXTRACT(data, \"$.medical_record_type\")) AS medical_record_type,
        JSON_UNQUOTE(JSON_EXTRACT(data, \"$.medical_record_id\")) AS medical_record_id
        FROM eligibility_jobs
        LEFT JOIN eligibility_batches on eligibility_batches.id = eligibility_jobs.batch_id
        ORDER BY eligibility_job_id DESC
      ");
    }

    /**
     * Get the name of the sql view.
     */
    public function getViewName(): string
    {
        return 'eligible_patients';
    }
}
