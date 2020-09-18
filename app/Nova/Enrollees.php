<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use CircleLinkHealth\Customer\CpmConstants;
use App\Nova\Actions\ImportEnrollee;
use App\Nova\Actions\ImportEnrollees;
use App\Nova\Actions\SelfEnrollmentManualInvite;
use App\Nova\Filters\EnrolleeStatus;
use App\Nova\Filters\PatientAutoEnrollmentStatus;
use App\Nova\Filters\PracticeFilter;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Http\Request;
use Jubeki\Nova\Cards\Linkable\LinkableAway;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;

class Enrollees extends Resource
{
    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = CpmConstants::NOVA_GROUP_ENROLLMENT;

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
            new ImportEnrollees(),
            new SelfEnrollmentManualInvite(),
            //try to implement in a future date - coordinate with Zach
            //            new MarkEnrolleesForAutoEnrollment(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @return array
     */
    public function cards(Request $request)
    {
        //adds templates to new Actions
        return [
            (new LinkableAway())
                ->title('Create Patients CSV Template')
                ->url('https://drive.google.com/file/d/1RgCl5AgyodKlIytemOVMXlAHgr9iGgm9/view?usp=sharing')
                ->subtitle('Click to download.')
                ->target('_self'),
            (new LinkableAway())
                ->title('Auto Enrolment CSV Template')
                ->url('https://drive.google.com/file/d/1qEF-p6PB4q_gI6q0i3JQAwZeIHwfSRop/view?usp=sharing')
                ->subtitle('Click to download.')
                ->target('_self'),
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
                ->sortable(),

            Text::make('Status')
                ->sortable()
                ->hideWhenCreating(),

            Text::make('Address')
                ->sortable()
                ->creationRules('string')
                ->updateRules('string'),

            Number::make('MRN')
                ->sortable()
                ->creationRules('required', 'integer')
                ->updateRules('integer'),

            Text::make('Primary Insurance')
                ->sortable()
                ->creationRules('string')
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
            new PatientAutoEnrollmentStatus(),
        ];
    }

    /**
     * @return string
     */
    public static function label()
    {
        return 'Enrollees';
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
