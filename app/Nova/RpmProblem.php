<?php

namespace App\Nova;

use App\Nova\Actions\ImportRpmProblems;
use Illuminate\Http\Request;
use Jubeki\Nova\Cards\Linkable\LinkableAway;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class RpmProblem extends Resource
{
    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = \App\Constants::NOVA_GROUP_PRACTICES;
    
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \CircleLinkHealth\Eligibility\Entities\RpmProblem::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make(__('ID'), 'id')->sortable(),
            
            BelongsTo::make('practice', 'practice', Practice::class),
            
            Text::make('code_type'),
            
            Text::make('code'),
            
            Text::make('description')
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [
            (new LinkableAway())
                ->title('Upload RPM Problems CSV Template')
                ->url('https://docs.google.com/spreadsheets/d/1xPLu7v-4F4Xmu65GJ5CLr8quatjwPYhqdfra_qF6hbM/edit?usp=sharing')
                ->subtitle('Click to download.')
                ->target('_self'),
        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [
            new ImportRpmProblems()
        ];
    }
}
