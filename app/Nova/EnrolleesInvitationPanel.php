<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use App\EnrollableInvitationLink;
use App\Nova\Filters\EnrolleeInvitationFilter;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Circlelinkhealth\EnrollmentInvites\EnrollmentInvites;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphOne;
use Laravel\Nova\Fields\Text;

class EnrolleesInvitationPanel extends Resource
{
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

            //            MorphOne::make(EnrollableInvitationLink::class, ),
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

    private function getPracticeId()
    {
        $url = parse_url($_SERVER['HTTP_REFERER']);
        parse_str($url['query'], $params);

        return $params['practice_id'];
    }
}
