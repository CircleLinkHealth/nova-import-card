<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use CircleLinkHealth\Core\Entities\DatabaseNotification;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Notification extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = DatabaseNotification::class;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'type',
        'mail_status',
        'twilio_status',
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

    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    public function authorizedToDelete(Request $request)
    {
        return false;
    }

    public function authorizedToUpdate(Request $request)
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
            Text::make('ID', 'id')->sortable(),

            Text::make('Type', 'type')->sortable(),

            Number::make('Notifiable ID', 'notifiable_id')->sortable(),

            Text::make('Notifiable Type', 'notifiable_type')->sortable(),

            Text::make('Email Status', 'mail_status')->sortable(),

            Text::make('Email Status Details', 'mail_details')->sortable(),

            Text::make('Email ID', 'mail_smtp_id')->sortable(),

            Text::make('Email SendGrid ID', 'mail_sg_message_id')->sortable(),

            Text::make('SMS Status', 'twilio_status')->sortable(),

            Text::make('SMS Status Details', 'twilio_details')->sortable(),

            Text::make('SMS ID', 'twilio_sid')->sortable(),

            DateTime::make('Created At', 'created_at')->sortable(),

            DateTime::make('Updated At', 'updated_at')->sortable(),
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

    public static function indexQuery(NovaRequest $request, $query)
    {
        if (empty($request->get('orderBy'))) {
            $query->getQuery()->orders = [];

            return $query->orderBy('updated_at', 'desc');
        }

        return $query;
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
