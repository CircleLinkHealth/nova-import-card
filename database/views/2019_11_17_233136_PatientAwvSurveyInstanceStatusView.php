<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\SqlViews\BaseSqlView;

class PatientAwvSurveyInstanceStatusView extends BaseSqlView
{
    /**
     * Create the sql view.
     */
    public function createSqlView(): bool
    {
        return \DB::statement("
        CREATE VIEW {$this->getViewName()}
        AS
        SELECT u.id as patient_id,
               u.display_name as patient_name,
               u.program_id as practice_id,
               pi.birth_date as dob,
               ctm.provider_name,
               hra.status as hra_status,
               CASE WHEN hra.completed_at is null
                    THEN hra.created_at
                    ELSE hra.completed_at
               END as hra_display_date,
               v.status as vitals_status,
               CASE WHEN v.completed_at is null
                    THEN v.created_at
                    ELSE v.completed_at
               END as v_display_date,
               CASE WHEN hra.year is null
                    THEN v.year
                    ELSE hra.year
               END as year
        FROM users u

        LEFT JOIN patient_info pi ON u.id=pi.user_id

        LEFT JOIN (SELECT ctm.member_user_id, ctm.type, ctm.user_id, u.display_name as provider_name from patient_care_team_members ctm left join
        (SELECT u.id, u.display_name from users u) u on ctm.member_user_id=u.id WHERE ctm.type='billing_provider') ctm on ctm.user_id=u.id

        LEFT JOIN (SELECT us.user_id, us.status, us.created_at, us.completed_at, si.year, s.name from users_surveys us LEFT JOIN survey_instances si on us.survey_instance_id=si.id
        LEFT JOIN surveys s on us.survey_id=s.id WHERE s.name='HRA') hra on hra.user_id=u.id
        LEFT JOIN (SELECT us.user_id, us.status, us.created_at, us.completed_at, si.year, s.name from users_surveys us LEFT JOIN survey_instances si on us.survey_instance_id=si.id
        LEFT JOIN surveys s on us.survey_id=s.id WHERE s.name='Vitals') v on v.user_id=u.id
        WHERE
        CASE WHEN (hra.year IS NULL AND v.year IS NOT NULL) OR (hra.year IS NOT NULL AND v.year IS NULL)
            THEN true
            ELSE hra.year = v.year
        END
        AND u.deleted_at is null
      ");
    }

    /**
     * Get the name of the sql view.
     */
    public function getViewName(): string
    {
        return 'patient_awv_survey_instance_status_view';
    }
}
