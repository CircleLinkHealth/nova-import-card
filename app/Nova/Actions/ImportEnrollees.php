<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Actions;

use Anaseqal\NovaImport\Actions\Action;
use App\Nova\Importers\Enrollees;
use App\Practice;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\Select;
use Maatwebsite\Excel\Facades\Excel;

class ImportEnrollees extends Action
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
            Select::make('Action Type', 'action_type')->options([
                'create_enrollees'         => 'Create Patients from CSV',
                'mark_for_auto_enrollment' => 'Mark Patients for Auto Enrollment',
            ]),
            File::make('File')
                ->rules('required'),
            Select::make('Practice', 'practice_id')->options($practices)->withModel(Practice::class),
        ];
    }

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $file = $fields->file;
        Excel::import(new Enrollees($fields->practice_id, $fields->action_type, $file->getClientOriginalName()), $file);

        return Action::message('It worked!');
    }

    /**
     * Works for inside of card.
     *
     * @return string
     */
    public function name()
    {
        return __('Import CSV');
    }

    public function uriKey(): string
    {
        return 'import-enrollees';
    }
}
