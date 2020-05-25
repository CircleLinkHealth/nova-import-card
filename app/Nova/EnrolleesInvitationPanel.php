<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use App\Helpers\SelfEnrollmentHelpers;
use App\Nova\Actions\SelfEnrollmentManualInvite;
use App\Nova\Metrics\AllInvitesButtonColor;
use App\Nova\Metrics\SelfEnrolledButtonColor;
use App\Nova\Metrics\SelfEnrolledPatientTotal;
use App\Nova\Metrics\TotalInvitationsSentHourly;

use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Circlelinkhealth\EnrollmentInvites\EnrollmentInvites;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class EnrolleesInvitationPanel extends Resource
{
    

    const COMPLETED = 'completed';

    const IN_PROGRESS = 'in_progress';
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = Enrollee::class;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'first_name', 'last_name', 'status',
    ];

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    public static $with = ['selfEnrollmentStatus', 'enrollmentInvitationLinks', 'user.patientInfo', 'statusRequestsInfo'];

    /**
     * Get the actions available for the resource.
     *
     * @return array
     */
    public function actions(Request $request)
    {
        return [
            (new SelfEnrollmentManualInvite())->canSee(function () {
                return true;
            })->canRun(function () {
                return true;
            }),
        ];
    }

    /**
     * @return bool
     */
    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    /**
     * @return bool
     */
    public function authorizedToDelete(Request $request)
    {
        return false;
    }

    /**
     * @return bool
     */
    public function authorizedToUpdate(Request $request)
    {
        return false;
    }

    /**
     * @return bool
     */
    public static function availableForNavigation(Request $request)
    {
        return false;
    }

    /**
     * Get the cards available for the request.
     *
     * @return array
     */
    public function cards(Request $request)
    {
        return [
            (new EnrollmentInvites())->withMeta(
                [
                    'practice_id' => self::getPracticeId($this),
                    'is_patient'  => false,
                ]
            ),
            (new SelfEnrolledPatientTotal(self::getPracticeId($this))),
            (new SelfEnrolledButtonColor(self::getPracticeId($this))),
            (new AllInvitesButtonColor(self::getPracticeId($this))),
            (new TotalInvitationsSentHourly(self::getPracticeId($this))),
        ];
    }

    /**
     * Get the fields displayed by the resource.y.
     *
     * @return array
     */
    public function fields(Request $request)
    {
        $surveyInstance = $this->getSurveyInstance();

        $awvUserSurvey = null;

        if ( ! is_null($this->resource->user)) {
            $awvUserSurvey = SelfEnrollmentHelpers::awvUserSurveyQuery($this->resource->user, $surveyInstance)->first();
        }

        $firstEnrollmentInvitationLink = $this->resource->enrollmentInvitationLinks->isNotEmpty()
            ? $this->resource->enrollmentInvitationLinks->sortBy('created_at')->first()
            : null;

        $enroleeHasLoggedIn = $this->enrolleeHasLoggedIn();

        $inviteSentDate = ! is_null($firstEnrollmentInvitationLink) && ! is_null($firstEnrollmentInvitationLink->created_at)
            ? $firstEnrollmentInvitationLink->created_at->toDateString()
            : 'N/A';

        $requestedInfoDate = ! is_null($this->resource->statusRequestsInfo) && ! is_null($this->resource->statusRequestsInfo->created_at)
            ? $this->resource->statusRequestsInfo->created_at->toDateString()
            : 'N/A';

        $enrolledDate = Enrollee::ENROLLED === $this->resource->status && $this->resource->user && $this->resource->user->patientInfo && ! is_null($this->resource->user->patientInfo->registration_date)
        ? $this->resource->user->patientInfo->registration_date->toDateString()
            : 'N/A';

        return [
            ID::make()->sortable(),

            Text::make('First name', 'first_name')
                ->sortable(),

            Text::make('Last name', 'last_name')
                ->sortable(),

            Date::make('Date Sent', function () use ($inviteSentDate) {
                return $inviteSentDate;
            }),

            Date::make('Date Asked For Call', function () use ($requestedInfoDate) {
                return $requestedInfoDate;
            }),

            Date::make('Date Enrolled', function () use ($enrolledDate) {
                return  $enrolledDate;
            }),

            Boolean::make('Invited', function () use ($firstEnrollmentInvitationLink) {
                return ! is_null($firstEnrollmentInvitationLink);
            }),

            Boolean::make('Has viewed login form', function () {
                return $this->resource->enrollmentInvitationLinks->where('manually_expired', true)->isNotEmpty();
            }),

            Boolean::make('Has viewed Letter', function () use ($enroleeHasLoggedIn) {
                return $enroleeHasLoggedIn;
            }),
            Boolean::make('Requested Call', function () use ($requestedInfoDate) {
                return ! ('N/A' === $requestedInfoDate);
            }),
            Boolean::make("Has clicked 'Get my Care Coach'", function () use ($enroleeHasLoggedIn) {
                if (is_null($this->resource->user)) {
                    return false;
                }

                if ( ! $enroleeHasLoggedIn) {
                    return false;
                }

                $survey = SelfEnrollmentHelpers::getEnrolleeSurvey();

                if (empty($survey)) {
                    return false;
                }

                $surveyInstance = $this->getSurveyInstance();

                return SelfEnrollmentHelpers::awvUserSurveyQuery($this->resource->user, $surveyInstance)->exists();
            }),

            Boolean::make('Survey in progress', function () use ($enroleeHasLoggedIn) {
                if ( ! $enroleeHasLoggedIn) {
                    return false;
                }

                return self::IN_PROGRESS === optional($this->resource->selfEnrollmentStatus)->awv_survey_status;
            }),

            Boolean::make('Survey Completed', function () use ($enroleeHasLoggedIn) {
                if ( ! $enroleeHasLoggedIn) {
                    return false;
                }

                return self::COMPLETED === optional($this->resource->selfEnrollmentStatus)->awv_survey_status;
            }),

            Boolean::make('Enrolled', function () {
                return Enrollee::ENROLLED === $this->resource->status;
            }),
        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array
     */
    public function filters(Request $request)
    {
        return [
        ];
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->whereIn('status', [
            Enrollee::QUEUE_AUTO_ENROLLMENT,
            Enrollee::UNREACHABLE,
            Enrollee::ENROLLED,
            Enrollee::TO_CALL,
        ])->where('practice_id', self::getPracticeId())
            ->where(function ($q) {
                $q->where('source', '!=', Enrollee::UNREACHABLE_PATIENT)
                    ->orWhereNull('source');
            })
            ->has('selfEnrollmentStatus');
    }

    /**
     * @return string
     */
    public static function label()
    {
        return 'Invitations Panel';
    }

    /**
     * Get the lenses available for the resource.
     *
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    private function enrolleeHasLoggedIn(): bool
    {
        return $this->resource->selfEnrollmentStatus && $this->resource->selfEnrollmentStatus->logged_in;
    }

    private static function getPracticeId(?EnrolleesInvitationPanel $resource = null)
    {
        $url = parse_url($_SERVER['HTTP_REFERER']);

        if (is_array($url) && array_key_exists('query', $url)) {
            parse_str($url['query'], $params);

            return $params['practice_id'];
        }

        if ($resource) {
            return $resource->resource->practice_id;
        }

        return null;
    }

    private function getSurveyInstance()
    {
        $survey = SelfEnrollmentHelpers::getEnrolleeSurvey();

        return DB::table('survey_instances')
            ->where('survey_id', '=', $survey->id)
            ->first();
    }
}
