<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use App\Constants;
use App\Nova\Actions\ImportEnrollee;
use App\Nova\Importers\EnroleeData as EnroleeDataImporter;
use CircleLinkHealth\ClhImportCardExtended\ClhImportCardExtended;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Http\Request;
use Jubeki\Nova\Cards\Linkable\LinkableAway;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;

class EnroleeData extends Resource
{
    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = Constants::NOVA_GROUP_ENROLLMENT;

    public static $importer = EnroleeDataImporter::class;

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
            new ImportEnrollee
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @return array
     */
    public function cards(Request $request)
    {
        $practices = Practice::whereIn('id', auth()->user()->viewableProgramIds())
            ->activeBillable()
            ->pluck('display_name', 'id')
            ->toArray();

        $cards = [
            new ClhImportCardExtended(self::class, [
                Select::make('practice')->options($practices)->withModel(Practice::class),
            ], 'Patients from CSV'),
        ];

        if ( ! isProductionEnv()) {
            $cards[] = (new LinkableAway())
                ->title('Create Patients')
                ->url(route('ca-director.test-enrollees'))
                ->subtitle('(Creates 10 test patients)')
                ->target('_blank');
        }

        return $cards;
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            BelongsTo::make('Provider', 'provider', User::class)
                ->sortable(),

            Text::make('First Name')
                ->sortable()
                ->creationRules('required', 'string')
                ->updateRules('string'),

            Text::make('Last Name')
                ->sortable()
                ->creationRules('required', 'string')
                ->updateRules('string'),
            
            ID::make('Eligible Pt ID','id')->sortable(),

            Text::make('Address')
                ->sortable()
                ->creationRules('required', 'string')
                ->updateRules('string'),

            Number::make('MRN')
                ->sortable()
                ->creationRules('required', 'integer')
                ->updateRules('integer'),

            Date::make('DOB')
                ->sortable()
                ->format('MM/DD/YYYY')->creationRules('required', 'date')
                ->updateRules('date'),

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
        return [];
    }

    /**
     * @return string
     */
    public static function label()
    {
        return 'Patients - Create';
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
