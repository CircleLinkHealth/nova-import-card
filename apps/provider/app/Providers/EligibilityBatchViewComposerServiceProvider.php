<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use View;

class EligibilityBatchViewComposerServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     */
    public function boot()
    {
        View::composer(['eligibilityBatch.index'], function ($view) {
            $data = collect($view->getData());

            $batches = $data->get('batches')->map(function ($b) {
                switch ($b->status) {
                    case 0:
                        $b->cssClass = 'warning';
                        break;
                    case 1:
                        $b->cssClass = 'info';
                        break;
                    case 2:
                        $b->cssClass = 'alert';
                        break;
                    case 3:
                        $b->cssClass = 'success';
                        break;
                    default:
                        $b->cssClass = 'default';
                        break;
                }

                $b->statusPretty = snakeToSentenceCase($b->getStatus());

                return $b;
            });

            $view->with(compact([
                'batches',
            ]));
        });
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
    }
}
