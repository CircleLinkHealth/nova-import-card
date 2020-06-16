<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\Http\Controllers\Enrollment\SelfEnrollmentController;
use App\SelfEnrollment\Helpers;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\SqlViews\BaseSqlView;

class SelfEnrollmentMetricsView extends BaseSqlView
{
    /**
     * Create the sql view.
     *
     * @throws Exception
     */
    public function createSqlView(): bool
    {
        $this->createSurveyIfTestingEnvironment();
        $enrolled        = Enrollee::ENROLLED;
        $toCall          = Enrollee::TO_CALL;
        $defaultBtnColor = SelfEnrollmentController::DEFAULT_BUTTON_COLOR;
        $red             = SelfEnrollmentController::RED_BUTTON_COLOR;
        $manualInvite    = 'one-off_invitations';
        $green           = 'Green';
        $redString       = 'Red';
        $survey          = Helpers::getEnrolleeSurvey();
        $surveyInstance  = DB::table('survey_instances')
            ->where('survey_id', '=', $survey->id)
            ->first();

        $needle = ':';

        $showDemo = $this->showDemoPracticeDataOnly();

        return \DB::statement("
        CREATE VIEW {$this->getViewName()}
         AS
         SELECT
       b.id as batch_id,
       DATE_FORMAT(b.created_at, '%Y-%m-%d') batch_date,
       DATE_FORMAT(b.created_at,'%H:%i:%s') batch_time,
       p.display_name as practice_name,
       CASE WHEN b.type = '$defaultBtnColor' THEN '$green'
       WHEN b.type = '$red' THEN '$redString'
       WHEN b.type LIKE '%{$needle}%' AND SUBSTRING_INDEX(b.type, ':', '-1') = '$defaultBtnColor'
       THEN '$green'
       WHEN b.type LIKE '%{$needle}%' AND SUBSTRING_INDEX(b.type, ':', '-1') = '$red'
       THEN '$redString'
       WHEN b.type LIKE '%{$needle}%' AND SUBSTRING_INDEX(b.type, ':', '-1') = '$manualInvite'
       THEN '$green'
       END as button_color,
       COUNT(DISTINCT i.id) as total_invites_sent,
       SUM(i.manually_expired = true) as total_invites_opened,
       CONCAT(ROUND(SUM(i.manually_expired = true) * 100.0 / COUNT(DISTINCT i.id), 0), '%') as percentage_invites_opened,
       CAST(COUNT(l.user_id) as CHAR(50)) as total_seen_letter,
       CONCAT(IFNULL(ROUND((COUNT(l.id) * 100) / SUM(i.manually_expired = true)), 0), '%') as percentage_seen_letter,
       CAST(COUNT(DISTINCT us.user_id) as CHAR(50)) as total_seen_form,
       CONCAT(IFNULL(ROUND((SUM(case when us.survey_instance_id = $surveyInstance->id AND us.user_id = e.user_id then 1 else 0 end) * 100) / COUNT(l.user_id)), 0), '%') as percentage_seen_form,
       IFNULL(SUM(case when e.status = '$enrolled' AND us.status = 'completed' then 1 else 0 end), 0) as total_enrolled,
       CONCAT(IFNULL(ROUND((SUM(case when e.status = '$enrolled' AND us.status = 'completed' then 1 else 0 end) * 100) / COUNT(DISTINCT case when us.survey_instance_id = $surveyInstance->id AND us.user_id = e.user_id then 1 else 0 end)),0), '%') as percentage_enrolled,
       CAST(SUM(case when e.status = '$toCall' AND erf.enrollable_id = e.id then 1 else 0 end) as CHAR(50)) as total_call_requests,
       CONCAT(IFNULL(ROUND((SUM(case when e.status = '$toCall' AND erf.enrollable_id = e.id then 1 else 0 end) * 100) / COUNT(l.user_id)),0), '%') as percentage_call_requests
       
       FROM
       enrollables_invitation_links i
       
       left join enrollment_invitations_batches b on i.batch_id = b.id
       left join enrollees e on e.id = i.invitationable_id
       left join login_logout_events l on l.user_id = e.user_id
       left join practices p on b.practice_id = p.id
       left join users_surveys us on e.user_id = us.user_id
       left join enrollees_request_info erf on e.id = erf.enrollable_id
       
       -- Tables joining to has multiple rows for a single row in other tables:
       -- DISTINCT wil not help:
       
       WHERE 0 = (SELECT COUNT(e2.id)
             FROM enrollees e2
             WHERE e.user_id = e2.user_id
                AND e2.id < e.id)
                
       AND 0 = (SELECT COUNT(l2.id)
             FROM login_logout_events l2
             WHERE l.user_id = l2.user_id
                AND l2.id < l.id)
        AND
       p.is_demo = $showDemo

       GROUP BY
       batch_id
       
       ");
    }

    /**
     * Get the name of the sql view.
     */
    public function getViewName(): string
    {
        return 'self_enrollment_metrics_view';
    }

    /**
     * @throws Exception
     */
    private function createSurvey()
    {
        $survey = DB::table('surveys')->updateOrInsert(
            [
                'name'        => SelfEnrollmentController::ENROLLEES_SURVEY_NAME,
                'description' => 'Enrollees Survey',
            ]
        );

        if ( ! $survey) {
            throw new \Exception('Could not create survey with name '.SelfEnrollmentController::ENROLLEES_SURVEY_NAME);
        }
    }

    /**
     * @throws Exception
     */
    private function createSurveyIfTestingEnvironment()
    {
        if (\Illuminate\Support\Facades\App::environment(['testing'])) {
            $this->createSurvey();
            $survey = $this->enrolleesSurvey();
            $this->createSurveyInstance($survey->id);
        }
    }

    /**
     * @param $surveyId
     *
     * @throws Exception
     */
    private function createSurveyInstance($surveyId)
    {
        $surveyInstance = DB::table('survey_instances')->updateOrInsert([
            'survey_id' => $surveyId,
            'year'      => now()->year,
        ]);

        if ( ! $surveyInstance) {
            throw new \Exception('Could not find survey instance for survey id '.$surveyId);
        }
    }

    /**
     * @throws Exception
     *
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|object
     */
    private function enrolleesSurvey()
    {
        $survey = DB::table('surveys')
            ->where('name', SelfEnrollmentController::ENROLLEES_SURVEY_NAME)
            ->first();

        if (empty($survey)) {
            throw new \Exception('Could not find survey with name '.SelfEnrollmentController::ENROLLEES_SURVEY_NAME);
        }

        return $survey;
    }

    private function showDemoPracticeDataOnly()
    {
        return (isSelfEnrollmentTestModeEnabled() || \Illuminate\Support\Facades\App::environment(['local', 'testing'])) ? 1 : 0;
    }
}
