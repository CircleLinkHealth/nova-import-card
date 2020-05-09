<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use App\Traits\EnrollableManagement;
use CircleLinkHealth\Customer\Traits\HasEnrollableInvitation;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Circlelinkhealth\EnrollmentInvites\EnrollmentInvites;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class EnrolleesInvitationPanel extends Resource
{
    use EnrollableManagement;
    use HasEnrollableInvitation;
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
        'id', 'first_name', 'last_name',
    ];
    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    public static $with = ['selfEnrollmentStatuses'];

    /**
     * Get the actions available for the resource.
     *
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
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
                    'practice_id' => $this->getPracticeId(),
                    'is_patient'  => false,
                ]
            ),
        ];
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(Request $request)
    {
        $lastInvitationLink = $this->getLastEnrollmentInvitationLink();

        return [
            ID::make()->sortable(),

            Text::make('First name', 'first_name')
                ->sortable(),

            Text::make('Last name', 'last_name')
                ->sortable(),

            Boolean::make('Invited', function () use ($lastInvitationLink) {
                return ! is_null($lastInvitationLink);
            }),

            Boolean::make('Has viewed login form', function () use ($lastInvitationLink) {
                if (is_null($lastInvitationLink)) {
                    return false;
                }
                if ( ! $lastInvitationLink->manually_expired
                    && is_null(optional($this->selfEnrollmentStatuses)->awv_survey_status)) {
                    return false;
                }

                return true;
            }),

            Boolean::make('Has viewed Letter', function () {
                $userId = $this->resource->user_id;
                if ($this->enrolleeHasNotLoggedIn($userId)) {
                    return false;
                }

                return optional($this->selfEnrollmentStatuses)->logged_in;
            }),
            Boolean::make('Requested Call', function () {
                return $this->statusRequestsInfo()->exists();
            }),
            Boolean::make("Has clicked 'Get my Care Coach'", function () {
                $userId = $this->resource->user_id;
                if ($this->enrolleeHasNotLoggedIn($userId)) {
                    return false;
                }

                return $this->selfEnrollmentStatuses && optional($this->selfEnrollmentStatuses)->logged_in
                    && is_null($this->selfEnrollmentStatuses->awv_survey_status);
            }),

            Boolean::make('Survey in progress', function () {
                $userId = $this->resource->user_id;
                if ($this->enrolleeHasNotLoggedIn($userId)) {
                    return false;
                }

                return self::IN_PROGRESS === optional($this->selfEnrollmentStatuses)->awv_survey_status;
            }),

            Boolean::make('Survey Completed', function () {
                $userId = $this->resource->user_id;
                if ($this->enrolleeHasNotLoggedIn($userId)) {
                    return false;
                }

                return self::COMPLETED === optional($this->selfEnrollmentStatuses)->awv_survey_status;
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
            ->whereHas('selfEnrollmentStatuses');
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

    private function enrolleeHasNotLoggedIn($userId)
    {
        return is_null($userId) && ! optional($this->selfEnrollmentStatuses)->logged_in;
    }

    private static function getPracticeId()
    {
        $url = parse_url($_SERVER['HTTP_REFERER']);
        parse_str($url['query'], $params);

        return $params['practice_id'];
    }

    private function getSurveyInstance()
    {
        $survey = $this->getEnrolleeSurvey();

        if (empty($survey)) {
            return response()->json(
                'Enrollee Survey is missing'
            );
        }

        return DB::table('survey_instances')
            ->where('survey_id', '=', $survey->id)
            ->first();
    }
}
