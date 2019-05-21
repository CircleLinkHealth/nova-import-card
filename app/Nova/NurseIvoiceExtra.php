<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use App\NurseInvoiceExtra;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;

class NurseIvoiceExtra extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = NurseInvoiceExtra::class;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'user_id',
    ];

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'user_id';

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

    public static function authorizedToCreate(Request $request)
    {
        return true;
    }

    public function authorizedToDelete(Request $request)
    {
        return true;
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
        return [];
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
            ID::make()->sortable(),

            BelongsTo::make('user')
                ->hideWhenUpdating()
                ->hideFromIndex()
                /*->searchable()
                ->prepopulate()*/
                ->sortable(),

            Text::make('Name', 'user.display_name')
                ->sortable()
                ->hideWhenCreating()
                ->readonly(true),

            Date::make("Extra's Date", 'date')
                ->sortable(),

            Select::make('Unit', 'unit')
                ->options([
                    'minutes' => 'Minutes',
                    'usd'     => '$',
                ]),

            Text::make('unit')
                ->sortable()
                ->hideWhenUpdating()
                ->hideFromIndex()
                ->hideFromDetail()
                ->hideWhenCreating(),

            Text::make('Value', 'value')
                ->sortable(),

            Text::make('+ Days Payment', 'nurse.pay_interval')->hideWhenCreating()->hideWhenUpdating(),

            Boolean::make('Demo Nurse', 'nurse.is_demo')->hideWhenCreating()->hideWhenUpdating(),
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

    public static function label()
    {
        return 'Care Coach Bonuses';
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
