<?php

namespace App\Providers;

use CircleLinkHealth\Core\ChunksEloquentBuilder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
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

        QueryBuilder::macro(
            'toRawSql',
            function () {
                return array_reduce(
                    $this->getBindings(),
                    function ($sql, $binding) {
                        return preg_replace(
                            '/\?/',
                            is_numeric($binding)
                                ? $binding
                                : "'".$binding."'",
                            $sql,
                            1
                        );
                    },
                    $this->toSql()
                );
            }
        );

        EloquentBuilder::macro(
            'toRawSql',
            function () {
                return $this->getQuery()->toRawSql();
            }
        );
    }
}
