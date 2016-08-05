<?php

namespace EGALL\EloquentPHPUnit;

use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * DatabaseTestHelper Class.
 *
 * @author Erik Galloway <erik@mybarnapp.com>
 */
trait DatabaseTestHelper
{
    use DatabaseTransactions;

    /**
     * Run the test database migrations.
     */
    public function runDatabaseMigrations()
    {
        $this->artisan('migrate');

        $this->beforeApplicationDestroyed(function () {

            $this->artisan('migrate:reset');

        });
    }

    /**
     * Get the default seeder class name.
     * 
     * @return string
     */
    protected function getDefaultSeeder()
    {
        if (property_exists($this, 'defaultSeeder')) {
            return $this->defaultSeeder;
        }

        return 'DatabaseSeeder';
    }

    /**
     * Get the database seeders.
     * 
     * @return array
     */
    protected function getSeeders()
    {
        if (property_exists($this, 'seeders')) {
            return $this->seeders;
        }

        return [$this->defaultSeeder];
    }

    /**
     * Run the app's database seeders.
     * 
     * @return void
     */
    protected function runDatabaseSeeders()
    {
        foreach ($this->getSeeders() as $seeder) {
            $this->seed($seeder);
        }
    }
    /**
     * Setup the database before the tests.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->setUpDatabase();
    }

    protected function seedDatabase()
    {
        if ($this->shouldSeedDatabase()) {
            $this->runDatabaseSeeders();
        }
    }

    /**
     * Setup the database.
     * 
     * @return void
     */
    protected function setUpDatabase()
    {
        $this->runDatabaseMigrations();
        $this->seedDatabase();
    }

    /**
     * Should the database seeders be run.
     * 
     * @return bool
     */
    protected function shouldSeedDatabase()
    {
        if (property_exists($this, 'seedDatabase')) {
            return $this->seedDatabase;
        }

        return true;
    }
}
