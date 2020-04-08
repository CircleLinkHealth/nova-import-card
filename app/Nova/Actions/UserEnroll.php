<?php

namespace App\Nova\Actions;

use CircleLinkHealth\Customer\Entities\Patient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class UserEnroll extends Action implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $name = 'Enroll';

    /**
     * Perform the action on the given models.
     *
     * @param \Laravel\Nova\Fields\ActionFields $fields
     * @param \Illuminate\Support\Collection $models
     *
     * @return void
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $userIds = $models->pluck('id');
        Patient::whereIn('user_id', $userIds)->update(['ccm_status' => Patient::ENROLLED]);
        $models->each(function ($model) {
            $this->markAsFinished($model);
        });
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
