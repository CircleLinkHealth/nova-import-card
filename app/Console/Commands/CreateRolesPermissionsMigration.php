<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class CreateRolesPermissionsMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:rpm';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a migration to run RolesPermissions seeder.';
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();
        $this->filesystem = $filesystem;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle()
    {
        $stub = $this->getStub();

        $name = 'UpdateRolesAndPermissions' . Carbon::now()->timestamp;
        $path = $this->getMigrationPath();

        $this->filesystem->put(
            $path = $this->getPath($name, $path),
            $this->populateStub($name, $stub)
        );

        $this->comment("Created $path");
    }

    /**
     * Get the file containing the template to create a migration for Roles and Permissions
     *
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function getStub()
    {
        return $this->filesystem->get($this->stubPath());
    }

    /**
     * Get the path to the stubs.
     *
     * @return string
     */
    public function stubPath()
    {
        return __DIR__ . '/stubs/RolesPermissionsMigration/create.stub';
    }

    /**
     * Get the path to the migration directory.
     *
     * @return string
     */
    protected function getMigrationPath()
    {
        return app()->databasePath() . DIRECTORY_SEPARATOR . 'migrations';
    }

    /**
     * Get the full path to the migration.
     *
     * @param  string $name
     * @param  string $path
     *
     * @return string
     */
    protected function getPath($name, $path)
    {
        return $path . '/' . $this->getDatePrefix() . '_' . $name . '.php';
    }

    /**
     * Get the date prefix for the migration.
     *
     * @return string
     */
    protected function getDatePrefix()
    {
        return date('Y_m_d_His');
    }

    /**
     * Populate the place-holders in the migration stub.
     *
     * @param  string $name
     * @param  string $stub
     *
     * @return string
     */
    protected function populateStub($name, $stub)
    {
        $stub = str_replace('DummyClass', $this->getClassName($name), $stub);

        return $stub;
    }

    /**
     * Get the class name of a migration name.
     *
     * @param  string $name
     *
     * @return string
     */
    protected function getClassName($name)
    {
        return Str::studly($name);
    }
}
