<?php

namespace App\Nova;

use App\CcdaView;
use App\Constants;
use App\Nova\Actions\ClearAndReimportCcda;
use App\Nova\Actions\ImportCcdaAction;
use App\Nova\Filters\PracticeFilter;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Ccda extends Resource
{
    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = Constants::NOVA_GROUP_ENROLLMENT;
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = CcdaView::class;
    
    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'ccda_id';
    
    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'ccda_id',
        'patient_user_id',
        'mrn',
        'first_name',
        'last_name',
        'dm_from',
        'created_at',
    ];
    
    /**
     * Get the fields displayed by the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make('ccda_id')->sortable(),
            ID::make('patient_user_id')->sortable(),
            Text::make('Nurse', 'nurse_user_name')->sortable(),
            Text::make('Practice', 'practice_display_name')->sortable(),
            Text::make('Mrn', 'mrn')->sortable(),
            Text::make('First Name', 'first_name')->sortable(),
            Text::make('Last Name', 'last_name')->sortable(),
            Text::make('Source', 'source')->sortable(),
            Text::make('From (DM)', 'dm_from')->sortable(),
            Date::make('Created', 'created_at')->sortable(),
            Code::make('Errors', 'validation_errors')->json(),
        ];
    }
    
    /**
     * Get the cards available for the request.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [
        ];
    }
    
    /**
     * Get the filters available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [
            new PracticeFilter(),
        ];
    }
    
    /**
     * Get the lenses available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }
    
    /**
     * Get the actions available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [
            new ImportCcdaAction,
            new ClearAndReimportCcda,
        ];
    }
    
    public static function label()
    {
        return 'CCDAs';
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
        return strtolower((app(ImportCcdaAction::class))->name) === strtolower($request->input('action'));
    }
}
