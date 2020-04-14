<?php

namespace App\Nova\Actions;

use CircleLinkHealth\Eligibility\Jobs\ImportConsentedEnrollees;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class ImportEnrollee extends Action
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
        ImportConsentedEnrollees::dispatch($models->pluck('id')->all());
    
        return Action::message('');
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
