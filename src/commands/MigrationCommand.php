<?php namespace Michalisantoniou6\Cerberus;

/**
 * This file is part of Cerberus,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Michalisantoniou6\Cerberus
 */

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class MigrationCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'cerberus:migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a migration following the Cerberus specifications.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $this->laravel->view->addNamespace('cerberus', substr(__DIR__, 0, -8) . 'views');

        $rolesTable          = Config::get('cerberus.roles_table');
        $roleUserTable       = Config::get('cerberus.role_user_site_table');
        $permissionsTable    = Config::get('cerberus.permissions_table');
        $permissionRoleTable = Config::get('cerberus.permission_role_table');
        $sitesTable          = Config::get('cerberus.sites_table');

        $this->line('');
        $this->info("Tables: $rolesTable, $roleUserTable, $permissionsTable, $permissionRoleTable");

        $message = "A migration that creates '$rolesTable', '$roleUserTable', '$permissionsTable', '$permissionRoleTable'" .
                   " tables will be created in database/migrations directory";

        $this->comment($message);
        $this->line('');

        if ($this->confirm("Proceed with the migration creation? [Yes|no]", "Yes")) {

            $this->line('');

            $this->info("Creating migration...");
            if ($this->createMigration($rolesTable, $roleUserTable, $permissionsTable, $permissionRoleTable,
                $sitesTable)) {

                $this->info("Migration successfully created!");
            } else {
                $this->error(
                    "Couldn't create migration.\n Check the write permissions" .
                    " within the database/migrations directory."
                );
            }

            $this->line('');

        }
    }

    /**
     * Create the migration.
     *
     * @param string $name
     *
     * @return bool
     */
    protected function createMigration(
        $rolesTable,
        $roleUserTable,
        $permissionsTable,
        $permissionRoleTable,
        $sitesTable
    ) {
        $migrationFile = base_path("/database/migrations") . "/" . date('Y_m_d_His') . "_cerberus_setup_tables.php";

        //@todo: decide whether to keep this, or get user table from config
        $userModelName = Config::get('cerberus.user');
        $userModel     = new $userModelName();
        $usersTable    = $userModel->getTable();
        $userKeyName   = $userModel->getKeyName();


        $data = compact('rolesTable', 'roleUserTable', 'permissionsTable', 'permissionRoleTable', 'usersTable',
            'userKeyName', 'sitesTable');

        $output = $this->laravel->view->make('cerberus::generators.migration')->with($data)->render();

        if ( ! file_exists($migrationFile) && $fs = fopen($migrationFile, 'x')) {
            fwrite($fs, $output);
            fclose($fs);

            return true;
        }

        return false;
    }
}
