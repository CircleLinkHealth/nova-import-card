<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use App\Constants;
use App\Enrollee;
use App\Nova\Importers\EnroleeData as EnroleeDataImporter;
use Illuminate\Http\Request;
use Jubeki\Nova\Cards\Linkable\LinkableAway;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Sparclex\NovaImportCard\NovaImportCard;

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
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }

    /**
     * Get the cards available for the request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function cards(Request $request)
    {
        $cards = [new NovaImportCard(self::class)];

        if ( ! isProductionEnv()) {
            $cards[] = (new LinkableAway())
                    ->title('Create Patients')
                    ->url(route('ca-director.create-test-enrollees'))
                    ->subtitle('(Creates 100 test patients)')
                    ->target('_blank');
        }

        return $cards;
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param \Illuminate\Http\Request $request
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
     * @param \Illuminate\Http\Request $request
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
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }
}
