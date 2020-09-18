<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use CircleLinkHealth\SharedModels\Entities\Note;
use Circlelinkhealth\GenerateSuccessStoriesReport\GenerateSuccessStoriesReport;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Titasgailius\SearchRelations\SearchesRelations;

class NotesSuccessStories extends Resource
{
    use SearchesRelations;

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = \CircleLinkHealth\Customer\CpmConstants::NOVA_GROUP_PRACTICES;
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = Note::class;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'type',
    ];

    /**
     * The relationship columns that should be searched.
     *
     * @var array
     */
    public static $searchRelations = [
        'patient' => ['id', 'display_name', 'first_name', 'last_name'],
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
    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    /**
     * @return bool
     */
    public function authorizedToDelete(Request $request)
    {
        return false;
    }

    /**
     * @return bool
     */
    public function authorizedToUpdate(Request $request)
    {
        return true;
    }

    /**
     * Get the cards available for the request.
     *
     * @return array
     */
    public function cards(Request $request)
    {
        return [
            new GenerateSuccessStoriesReport(),
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
            ID::make()
                ->hideFromIndex()
                ->hideFromDetail()
                ->hideWhenCreating()
                ->hideWhenUpdating()
                ->sortable(),

            Text::make('Note id', 'id')
                ->hideWhenCreating()
                ->sortable()
                ->readonly(true),

            Text::make('Note Type', 'type')
                ->hideWhenCreating()
                ->readonly(true),

            Date::make('Date', 'performed_at')
                ->hideWhenCreating()
                ->readonly(true),

            Text::make('Patient', 'patient.display_name')
                ->hideFromIndex()
                ->hideFromDetail()
                ->hideWhenCreating()
                ->readonly(true),

            BelongsTo::make('Patient', 'patient', User::class)
                ->sortable()
                ->searchable()
                ->hideWhenUpdating()
                ->readonly(true),

            Boolean::make('Success Story', 'success_story')
                ->hideWhenCreating(),
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
            new \App\Nova\Filters\NotesSuccessStories(),
        ];
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
