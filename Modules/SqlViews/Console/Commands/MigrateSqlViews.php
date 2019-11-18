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
        $this->getViewFiles(scandir($this->getViewsDir()))->each(function ($className) {
            if (class_implements($className, SqlViewInterface::class)) {
                $this->warn("Running $className");
                $className::run();
                $this->line("Ran $className");
            }
        });
    }

    private function getViewsDir()
    {
        return database_path('views');
    }
}
