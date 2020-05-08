<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use App\Constants;
use App\Nova\Actions\ImportEnrolees;
use App\Nova\Actions\ImportEnrollee;
use App\Nova\Actions\MarkEnrolleesForAutoEnrollment;
use App\Nova\Filters\EnrolleeStatus;
use App\Nova\Filters\PracticeFilter;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Http\Request;
use Jubeki\Nova\Cards\Linkable\LinkableAway;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class EnroleeData extends Resource
{
    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = Constants::NOVA_GROUP_ENROLLMENT;

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
        'provider_id',
        'practice_id',
        'mrn',
        'first_name',
        'last_name',
        'address',
        'dob',
        'primary_insurance',
        'secondary_insurance',
        'id',
    ];

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'mrn';

    /**
     * Get the actions available for the resource.
     *
     * @return array
     */
    public function actions(Request $request)
    {
        return [
            new ImportEnrollee(),
            new ImportEnrolees(),
            new MarkEnrolleesForAutoEnrollment(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
//        if ( ! isProductionEnv()) {
//            $cards[] = (new LinkableAway())
//                ->title('Create Patients')
//                ->url(route('ca-director.test-enrollees'))
//                ->subtitle('(Creates 10 test patients)')
//                ->target('_blank');
//        }
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make('Eligible Pt ID', 'id')->sortable(),

            Text::make('First Name')
                ->sortable()
                ->creationRules('required', 'string')
                ->updateRules('string'),

            Text::make('Last Name')
                ->sortable()
                ->creationRules('required', 'string')
                ->updateRules('string'),

            BelongsTo::make('Provider', 'provider', User::class)
                ->sortable(),

            BelongsTo::make('Practice', 'practice', Practice::class)
                ->sortable(),

            Date::make('DOB')
                ->sortable()
                ->creationRules('required'),

            Text::make('Status')
                ->sortable()
                ->hideWhenCreating(),

            Text::make('Address')
                ->sortable()
                ->creationRules('required', 'string')
                ->updateRules('string'),

            Number::make('MRN')
                ->sortable()
                ->creationRules('required', 'integer')
                ->updateRules('integer'),

            Text::make('Primary Insurance')
                ->sortable()
                ->creationRules('required', 'string')
                ->updateRules('string'),

            Text::make('Secondary Insurance')
                ->sortable()
                ->creationRules('string')
                ->updateRules('string'),
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
            new PracticeFilter(),
            new EnrolleeStatus(),
        ];
    }

    /**
     * Build an "index" query for the given resource.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->whereNotIn('status', [Enrollee::LEGACY, Enrollee::INELIGIBLE, Enrollee::ENROLLED, Enrollee::SOFT_REJECTED, Enrollee::REJECTED]);
    }

    /**
     * @return string
     */
    public static function label()
    {
        return 'Patients to Enroll';
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
}
