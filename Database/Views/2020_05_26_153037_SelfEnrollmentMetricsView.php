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

       WHERE
       p.is_demo = $showDemo

       GROUP BY
       batch_id, batch_date, batch_time, practice_name, button_color
       
       
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
        return isSelfEnrollmentTestModeEnabled() || \Illuminate\Support\Facades\App::environment(['local', 'testing']);
    }
}
