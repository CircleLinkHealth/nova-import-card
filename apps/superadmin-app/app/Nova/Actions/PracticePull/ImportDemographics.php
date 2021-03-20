<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Actions\PracticePull;

use App\Nova\Importers\PracticePull\Demographics;
use App\Practice;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\Select;
use Maatwebsite\Excel\Facades\Excel;

class ImportDemographics extends Action
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Indicates if this action is only available on the resource detail view.
     *
     * @var bool
     */
    public $onlyOnIndex = true;

    public $standalone = true;

    /**
     * @var array
     */
    private $fields;

    public function __construct(array $fields = [])
    {
        $this->fields = $fields;
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        $practices = Practice::whereIn('id', auth()->user()->viewableProgramIds())
            ->activeBillable()
            ->pluck('display_name', 'id')
            ->toArray();

        return [
            File::make('File')
                ->rules('required'),
            Select::make('Practice', 'practice_id')->options($practices)->withModel(Practice::class),
        ];
    }

    /**
     * Perform the action.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields)
    {
        Excel::import(new Demographics($fields->practice_id), $fields->file);

        return Action::message('It worked!');
    }

    /**
     * Get the displayable name of the action.
     *
     * @return string
     */
    public function name()
    {
        return __('Import Practice Pull Demographics');
    }

    public function uriKey(): string
    {
        return 'import-practice-pull-demographics';
    }
}
