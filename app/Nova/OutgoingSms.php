<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use App\Constants;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;

class OutgoingSms extends Resource
{
    public static $group = Constants::NOVA_GROUP_ENROLLMENT;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\OutgoingSms';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'sender_user_id', 'receiver_phone_number', 'message',
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
            ID::make()->sortable(),
            Number::make('Sent By (User ID)', 'sender_user_id')->sortable()->hideWhenCreating(),
            Text::make('Receiver', 'receiver_phone_number')->sortable(),
            Textarea::make('Message', 'message')->sortable(),
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
        return 'Send SMS';
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
