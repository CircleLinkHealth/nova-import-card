<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\Core\Entities\AppConfig as AppConfigModel;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;

class AppConfig extends Resource
{
    public static $group = CpmConstants::NOVA_GROUP_SETTINGS;
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = AppConfigModel::class;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'config_key',
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
     * Get the cards available for the request.
     *
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            Text::make('Setting', 'config_key'),
            Text::make('Value', 'config_value'),
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

    public static function label()
    {
        return 'CPM Configuration';
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
