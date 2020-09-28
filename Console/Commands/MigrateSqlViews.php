<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SqlViews\Console\Commands;

use CircleLinkHealth\SqlViews\Contracts\SqlViewInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MigrateSqlViews extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate SQL Views';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:views';

    public function getViewFiles($paths)
    {
        return Collection::make($paths)->flatMap(function ($path) {
            return Str::endsWith($path, '.php') ? [$this->getViewName($path)] : [];
        })->filter()->sortBy(function ($file) {
            return $this->getViewName($file);
        })->values();
    }

    /**
     * Get the name of the migration.
     *
     * @param string $path
     *
     * @return string
     */
    public function getViewName($path)
    {
        return str_replace('.php', '', basename($path));
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->getViewFiles(scandir($this->getViewsDir()))->each(function ($filePath) {
            $class = $this->resolve($filePath);

            if (class_implements($class, SqlViewInterface::class)) {
                $this->warn("Running $filePath");
                $ran = $class::run();
                $this->line(($ran ? 'Ran ' : 'Did not ran ').$filePath);
            }
        });
    }

    /**
     * Resolve a view instance from a file.
     *
     * @param string $file
     *
     * @return object
     */
    public function resolve($file)
    {
        $class = Str::studly(implode('_', array_slice(explode('_', $file), 4)));

        return new $class();
    }

    private function getViewsDir()
    {
        return \Config::get('sqlviews.sql-views-directory');
    }
}
