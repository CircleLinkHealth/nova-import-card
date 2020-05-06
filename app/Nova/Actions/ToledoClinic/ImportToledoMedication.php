<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Actions\ToledoClinic;

use Anaseqal\NovaImport\Actions\Action;
use App\Nova\Importers\ToledoClinic\Medications;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\File;
use Maatwebsite\Excel\Facades\Excel;

class ImportToledoMedication extends Action
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
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            File::make('File')
                ->rules('required'),
        ];
    }

    /**
     * Perform the action.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields)
    {
        Excel::import(new Medications(), $fields->file);

        return Action::message('It worked!');
    }

    /**
     * Get the displayable name of the action.
     *
     * @return string
     */
    public function name()
    {
        return __('Import Toledo Medications');
    }

    public function uriKey(): string
    {
        return 'import-toledo-medications';
    }
}
