<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\SelfEnrollment\Helpers;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\SqlViews\BaseSqlView;

class SelfEnrollmentMetricsEnrollee extends BaseSqlView
{
    /**
     * Create the sql view.
     */
    public function createSqlView(): bool
    {
        $enrolled = Enrollee::ENROLLED;
        $toCall   = Enrollee::TO_CALL;

        $survey         = Helpers::getEnrolleeSurvey();
        $surveyInstance = DB::table('survey_instances')
            ->where('survey_id', '=', $survey->id)
            ->first();

        return \DB::statement("
        CREATE VIEW {$this->getViewName()}
         AS
         SELECT
       b.id as batch_id,
       DATE_FORMAT(b.created_at, '%Y-%m-%d') batch_date,
       DATE_FORMAT(b.created_at,'%H:%i:%s') batch_time,
       p.display_name as practice_name,
       b.type as button_color,
       COUNT(i.batch_id) as total_invites_sent,
       SUM(case when i.manually_expired = 1 then 1 else 0 end) as total_invites_opened,
       ROUND(SUM(case when i.manually_expired = 1 then 1 else 0 end) * 100.0 / COUNT(i.batch_id), 1) as percentage_invites_opened,
       COUNT(DISTINCT l.user_id) as total_saw_letter,
       ROUND((COUNT(DISTINCT l.user_id) * 100) / SUM(case when i.manually_expired = 1 then 1 else 0 end)) as percentage_saw_letter,
       SUM(case when us.survey_instance_id = $surveyInstance->id AND us.user_id = e.user_id then 1 else 0 end) as total_saw_form,
       ROUND((SUM(case when us.survey_instance_id = $surveyInstance->id AND us.user_id = e.user_id then 1 else 0 end) * 100) / COUNT(DISTINCT l.user_id)) as percentage_saw_form,
       SUM(case when e.status = '$enrolled' AND us.status = 'completed' then 1 else 0 end) as total_enrolled,
       ROUND((SUM(case when e.status = '$enrolled' AND us.status = 'completed' then 1 else 0 end) * 100) / SUM(case when us.survey_instance_id = $surveyInstance->id AND us.user_id = e.user_id then 1 else 0 end)) as percentage_enrolled,
       SUM(case when e.status = '$toCall' AND erf.enrollable_id = e.id then 1 else 0 end) as total_call_requests,
       ROUND((SUM(case when e.status = '$toCall' AND erf.enrollable_id = e.id then 1 else 0 end) * 100) / COUNT(DISTINCT l.user_id)) as percentage_call_requests
       
       FROM
       enrollables_invitation_links i

       left join enrollment_invitations_batches b on i.batch_id = b.id
       left join enrollees e on i.invitationable_id = e.id
       left join login_logout_events l on e.user_id = l.user_id
       left join practices p on b.practice_id = p.id
       left join users_surveys us on e.user_id = us.user_id
       left join enrollees_request_info erf on e.id = erf.enrollable_id
      
       GROUP BY
       batch_id, batch_date, batch_time, practice_name, button_color
        ");
    }

    /**
     * Get the name of the sql view.
     */
    public function getViewName(): string
    {
        return 'self_enrollment_metrics_enrollee';
    }
}
