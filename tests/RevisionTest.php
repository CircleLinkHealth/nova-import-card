<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Venturecraft\Revisionable\Tests;

use Venturecraft\Revisionable\Tests\Models\User;

class RevisionTest extends \Orchestra\Testbench\TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->loadLaravelMigrations(['--database' => 'testing']);

        // call migrations specific to our tests, e.g. to seed the db
        // the path option should be an absolute path.
        $this->loadMigrationsFrom([
            '--database' => 'testing',
            '--path'     => realpath(__DIR__.'/../src/migrations'),
        ]);
    }

    /**
     * Make sure revisions are created.
     */
    public function test_revisions_stored()
    {
        $user = User::create([
            'name'     => 'James Judd',
            'email'    => 'james.judd@revisionable.test',
            'password' => \Hash::make('456'),
        ]);

        // change to my nickname
        $user->update([
            'name' => 'Judd',
        ]);

        // change to my forename
        $user->update([
            'name' => 'James',
        ]);

        // we should have two revisions to my name
        $this->assertCount(2, $user->revisionHistory);
    }

    /**
     * Test we can interact with the database.
     */
    public function test_users_table()
    {
        User::create([
            'name'     => 'James Judd',
            'email'    => 'james.judd@revisionable.test',
            'password' => \Hash::make('456'),
        ]);

        $users = User::findOrFail(1);
        $this->assertEquals('james.judd@revisionable.test', $users->email);
        $this->assertTrue(\Hash::check('456', $users->password));
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }
}
