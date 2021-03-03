<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use CircleLinkHealth\Core\ChunksEloquentBuilder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrapThree();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //todo:move to core
        EloquentBuilder::macro(
            'chunkIntoJobs',
            function (int $limit, ShouldQueue $job) {
                if ( ! $job instanceof ChunksEloquentBuilder) {
                    throw new \Exception('The Query Builder macro "chunkIntoJobs" can only be called with jobs that implement the ChunksEloquentBuilder interface.');
                }

                $count = $this->count();
                $offset = 0;

                while ($offset < $count) {
                    dispatch(
                        $job->setOffset($offset)
                            ->setLimit($limit)
                    );
                    $offset = $offset + $limit;
                }
            }
        );
    }
}
