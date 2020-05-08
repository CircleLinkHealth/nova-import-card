<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use App\Nova\Filters\EnrolleeInvitationFilter;
use App\Traits\EnrollableManagement;
use CircleLinkHealth\Customer\Traits\HasEnrollableInvitation;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Circlelinkhealth\EnrollmentInvites\EnrollmentInvites;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;

class EnrolleesInvitationPanel extends Resource
{
    use EnrollableManagement;
    use HasEnrollableInvitation;
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
        'id',
    ];

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

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
        return [
            ID::make()->sortable(),

            Text::make('First name', 'first_name')
                ->sortable(),

            Text::make('Last name', 'last_name')
                ->sortable(),

            Boolean::make('Survey in progress', function () {
                if ( ! $this->checkIfForUserModelExists($this->resource->id)) {
                    return false;
                }

                return $this->hasSurveyInProgress($this->getUserModelEnrollee($this->resource->id));
            }),

            Boolean::make('Survey Completed', function () {
                if ( ! $this->checkIfForUserModelExists($this->resource->id)) {
                    return false;
                }

                return $this->hasSurveyCompleted($this->getUserModelEnrollee($this->resource->id));
            }),

            Boolean::make('Enrolled', function () {
                return Enrollee::ENROLLED === $this->resource->status;
            }),

            Boolean::make('Requested Call', function () {
                return /*$this->resource->statusRequestsInfo()->exists()*/ false;
            }),

            Boolean::make('Viewed Letter', function () {
                if ( ! $this->checkIfForUserModelExists($this->resource->id)) {
                    return false;
                }

                return $this->hasViewedLetterOrSurvey($this->getUserModelEnrollee($this->resource->id)->id);
            }),

            Boolean::make('Viewed Survey but not started', function () {
                if ( ! $this->checkIfForUserModelExists($this->resource->id)) {
                    return false;
                }

                return // $this->getSurveyInvitationLink($this->resource)->exists()
                    ! $this->hasSurveyInProgress($this->getUserModelEnrollee($this->resource->id))
                    && ! $this->hasSurveyCompleted($this->getUserModelEnrollee($this->resource->id));
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
        $practiceId = $this->getPracticeId();

        return [
            new EnrolleeInvitationFilter($practiceId),
        ];
    }

    /**
     * @return string
     */
    public static function label()
    {
        return 'Enrollee Invitations Panel';
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

    private function checkIfForUserModelExists($enrolleeId)
    {
        return empty($this->getUserModelEnrollee($enrolleeId)) ? false : true;
    }

    private function getPracticeId()
    {
        $url = parse_url($_SERVER['HTTP_REFERER']);
        parse_str($url['query'], $params);

        return $params['practice_id'];
    }
}
