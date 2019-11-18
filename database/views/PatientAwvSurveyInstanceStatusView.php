<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\SqlViews\Contracts\SqlViewInterface;

class PatientAwvSurveyInstanceStatusView implements SqlViewInterface
{
    /**
     * Drop and create Sql Views.
     *
     * @return mixed
     */
    public static function dropAndCreate()
    {
        $viewName = 'patient_awv_survey_instance_status_view';
        \DB::statement("DROP VIEW IF EXISTS ${viewName}");
        \DB::statement("
        CREATE VIEW ${viewName}
        AS
        SELECT
u.id as patient_id,
u.display_name as patient_name,
u.program_id as practice_id,
pi.birth_date as dob,
ctm.provider_name,
hra.status as hra_status,
if(hra.completed_at is null, hra.created_at, hra.completed_at) as hra_display_date,
v.status as vitals_status,
if(v.completed_at is null, v.created_at, v.completed_at) as v_display_date,
if(hra.year is null, v.year, hra.year) as year

from users u

LEFT JOIN patient_info pi ON u.id=pi.user_id

LEFT JOIN (SELECT ctm.member_user_id, ctm.type, ctm.user_id, u.display_name as provider_name from patient_care_team_members ctm left join
(SELECT u.id, u.display_name from users u) u on ctm.member_user_id=u.id WHERE ctm.type='billing_provider') ctm on ctm.user_id=u.id

LEFT JOIN (SELECT us.user_id, us.status, us.created_at, us.completed_at, si.year, s.name from users_surveys us LEFT JOIN survey_instances si on us.survey_instance_id=si.id
LEFT JOIN surveys s on us.survey_id=s.id WHERE s.name='HRA') hra on hra.user_id=u.id
LEFT JOIN (SELECT us.user_id, us.status, us.created_at, us.completed_at, si.year, s.name from users_surveys us LEFT JOIN survey_instances si on us.survey_instance_id=si.id
LEFT JOIN surveys s on us.survey_id=s.id WHERE s.name='Vitals') v on v.user_id=u.id
WHERE IF ((hra.year IS NULL AND v.year IS NOT NULL) OR (hra.year IS NOT NULL AND v.year IS NULL), true, hra.year = v.year)
AND u.deleted_at is null

      ");
    }
}
