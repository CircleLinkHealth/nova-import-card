<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Actions;

use App\Nova\Importers\SupplementalPatientDataImporter;
use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Anaseqal\NovaImport\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\Select;
use Maatwebsite\Excel\Facades\Excel;

class ImportSupplementalPatientData extends Action
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $onlyOnIndex = true;

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
        Excel::import(new SupplementalPatientDataImporter($fields->practice_id), $fields->file);

        return Action::message('It worked!');
    }

    /**
     * Get the displayable name of the action.
     *
     * @return string
     */
    public function name()
    {
        return __('Import Supplemental Patient Datas');
    }

    public function uriKey(): string
    {
        return 'import-supplemental-patient-datas';
    }
}
