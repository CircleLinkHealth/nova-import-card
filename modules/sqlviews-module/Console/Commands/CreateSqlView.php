<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SqlViews\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CreateSqlView extends Command
{
    /**
     * The Composer instance.
     *
     * @var \Illuminate\Support\Composer
     */
    protected $composer;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new sql view file';
    /**
     * @var Filesystem
     */
    protected $files;
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'make:sqlview {name : The name of the view}';

    /**
     * Create a new sql view file.
     */
    public function __construct(Composer $composer, Filesystem $files)
    {
        parent::__construct();

        $this->composer = $composer;
        $this->files    = $files;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $name = Str::studly(trim($this->input->getArgument('name')));

        $this->ensureViewDoesntAlreadyExist($name);

        $this->writeSqlView($name);

        $this->composer->dumpAutoloads();
    }

    /**
     * Ensure that an sql view with the given name doesn't already exist.
     *
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return void
     */
    protected function ensureViewDoesntAlreadyExist($name)
    {
        if (class_exists($className = $this->getClassName($name))) {
            $autoloader = require base_path('vendor/autoload.php');
            $file       = $autoloader->findFile($className);
            $classPath  = realpath($file);

            throw new InvalidArgumentException("Class `{$className}` already exists at `$classPath`.");
        }
    }

    /**
     * Get the class name of an sql view name.
     *
     * @param string $name
     *
     * @return string
     */
    protected function getClassName($name)
    {
        return Str::studly($name);
    }

    /**
     * Get the date prefix for the view.
     *
     * @return string
     */
    protected function getDatePrefix()
    {
        return date('Y_m_d_His');
    }

    /**
     * Get the sqk view stub file.
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->files->get($this->stubPath());
    }

    /**
     * Get sql views directory path.
     *
     * @return string
     */
    protected function getViewsDir()
    {
        return \Config::get('sqlviews.sql-views-directory');
    }

    /**
     * Populate the place-holders in the sql view stub.
     *
     * @param string $name
     * @param string $stub
     *
     * @return string
     */
    protected function populateStub($name, $stub)
    {
        return str_replace('dummy_view_name', $this->getViewName($name), str_replace('DummyClass', $this->getClassName($name), $stub));
    }

    /**
     * Write the sql view file to disk.
     *
     * @param string $name
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     *
     * @return string
     */
    protected function writeSqlView($name)
    {
        $stub = $this->getStub();

        $this->files->put(
            $path = $this->getPath($name),
            $this->populateStub($name, $stub)
        );

        $file = pathinfo(
            $path,
            PATHINFO_FILENAME
        );

        $this->line("<info>Created MySql View:</info> {$this->getPath($file)}");
    }

    private function getPath(string $name)
    {
        return $this->getViewsDir()."/{$this->getDatePrefix()}_$name.php";
    }

    private function getViewName(string $name)
    {
        return Str::snake($name);
    }

    private function stubPath()
    {
        return __DIR__.'/../../stubs/sqlview.stub';
    }
}
