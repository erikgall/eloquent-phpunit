<?php

namespace EGALL\EloquentPHPUnit;

use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * PHPUnit/Laravel database test helper.
 *
 * @author Erik Galloway <erik@mybarnapp.com>
 */
trait DatabaseTestHelper
{
    use DatabaseTransactions;

    /**
     * Run the test database migrations.
     *
     * @return $this
     */
    public function runDatabaseMigrations()
    {
        $this->artisan('migrate');

        $this->beforeApplicationDestroyed(function () {
            $this->artisan('migrate:reset');
        });

        return $this;
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

        return [$this->getDefaultSeeder()];
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
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    /**
     * Check if we should seed the database and call the seeders.
     *
     * @return void
     */
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
        $this->runDatabaseMigrations()->seedDatabase();
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
