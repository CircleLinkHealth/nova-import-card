<?php

namespace App\Nova\Actions;

use App\Jobs\ImportCcda;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class ImportCcdaAction extends Action implements ShouldQueue
{
    use InteractsWithQueue, Queueable;
    
    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Import';

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        Ccda::whereIn('id', $models->pluck('ccda_id')->all())->chunkById(50, function ($ccdas) {
            $ccdas->each(function (Ccda $ccda){
                $ccda->user_id = auth()->id();
                ImportCcda::dispatch($ccda, true);
            });
        });
    
        return Action::message('CCDAs queued to import');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [];
    }
}
