<?php

namespace App\Providers;

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
        $this->app->singleton('Illuminate\Contracts\Routing\ResponseFactory', function ($app) {
            return new \Illuminate\Routing\ResponseFactory(
                $app['Illuminate\Contracts\View\Factory'],
                $app['Illuminate\Routing\Redirector']
            );
        });
    }

    public function boot()
    {
        if ($this->app->environment() !== 'production') {
            app('config')->set('app.aliases', [
                // 'Validator' => 'Illuminate\Support\Facades\Validator',
                'Eloquent'  => 'Illuminate\Database\Eloquent\Model',
                // add other map you want
            ]);
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }
    }
}
