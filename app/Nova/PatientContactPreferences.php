<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use App\Nova\Importers\PatientContactPreferencesImporter;
use CircleLinkHealth\ClhImportCardExtended\ClhImportCardExtended;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;

class PatientContactPreferences extends Resource
{
    public static $group = \CircleLinkHealth\Customer\CpmConstants::NOVA_GROUP_ENROLLMENT;

    public static $importer = PatientContactPreferencesImporter::class;

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
    ];

    /**
     * Get the actions available for the resource.
     *
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }

    public static function authorizedToCreate(Request $request)
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
            ClhImportCardExtended::forUser(auth()->user(), self::class),
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
            Text::make('First Name'),
            Text::make('Last Name'),
            Text::make('Preferred Window'),
            Text::make('Preferred Days'),
            Text::make('Other Note'),
            Text::make('Mrn'),
            Text::make('DOB'),
            ID::make('Eligible Pt ID', 'id')->sortable(),
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
        return 'Upload Patient Preferences';
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
