<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\SqlViews\BaseSqlView;

class CcdasView extends BaseSqlView
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
        
        ccdas.id as ccda_id,
        ccdas.status as status,
        patient_user.id as patient_user_id,
        enrollees.id as enrollee_id,
        ccdas.practice_id as practice_id,
        ccdas.location_id as location_id,
        ccdas.billing_provider_id as billing_provider_id,
        ccdas.source as source,
        ccdas.validation_checks as validation_errors,
        
        nurse_user.id as nurse_user_id,
        nurse_user.display_name as nurse_user_name,
        
        practices.display_name as practice_display_name,
        practices.name as practice_name,
        
        direct_mail_messages.id as dm_id,
        direct_mail_messages.from as dm_from,
       
        CONCAT(REPLACE(JSON_UNQUOTE(REPLACE(JSON_EXTRACT(json, \"$.document.author.name.given\"),'[\"', '')), '\"]', ''), ' ', JSON_UNQUOTE(JSON_EXTRACT(json, \"$.document.author.name.family\"))) AS provider_name,
        DATE(JSON_UNQUOTE(JSON_EXTRACT(json, \"$.demographics.dob\"))) AS dob,
        JSON_UNQUOTE(JSON_EXTRACT(json, \"$.demographics.mrn_number\")) AS mrn,
        patient_user.first_name as patient_first_name,
        enrollees.first_name as enrollee_first_name,
REPLACE(
                REPLACE(
                   REPLACE(
                       JSON_UNQUOTE(json->\"$.demographics.name.given\"),
                       '[', ''
                   ),
                   ']', ''
                ),
                '\"', ''
            ) AS first_name,
                    patient_user.last_name as patient_last_name,
        enrollees.last_name as enrollee_last_name,
        JSON_UNQUOTE(JSON_EXTRACT(json, \"$.demographics.name.family\")) AS last_name,
        
        ccdas.created_at as created_at
    
        FROM ccdas
        LEFT JOIN practices on practices.id = ccdas.practice_id
        LEFT JOIN users as patient_user on patient_user.id = ccdas.patient_id
        LEFT JOIN patients_nurses on patients_nurses.patient_user_id = ccdas.patient_id
        LEFT JOIN users as nurse_user on patients_nurses.nurse_user_id = nurse_user.id
        LEFT JOIN enrollees on ccdas.id = enrollees.medical_record_id
        LEFT JOIN direct_mail_messages on direct_mail_messages.id = ccdas.direct_mail_message_id and direct_mail_messages.direction = 'received'
        ORDER BY ccda_id DESC
      ");
    }

    /**
     * Get the name of the sql view.
     */
    public function getViewName(): string
    {
        return 'ccdas_view';
    }
}
