<?php

namespace EGALL\EloquentPHPUnit;

use EGALL\EloquentPHPUnit\Database\Table;
use Illuminate\Foundation\Testing\TestCase as LaravelTestCase;

/**
 * Eloquent PhpUnit test case.
 * 
 * @author Erik Galloway <erik@mybarnapp.com>
 */
class EloquentTestCase extends LaravelTestCase
{
    use DatabaseTestHelper, ModelTestHelper, RelationshipTestHelper;

    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    /**
     * The subject's data.
     *
     * @var array
     */
    protected $data = [
        'casts'        => null,
        'dates'        => null,
        'fillable'    => null,
        'hidden'    => null,
        'table'        => null,
    ];

    /**
     * The test subject.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $subject;

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require $this->getBootstrapFilePath();

        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    /**
     * Set the test data.
     * 
     * @before
     * @return void
     */
    public function setUpEloquentModel()
    {
        $this->subject = (new $this->model());
        $this->setTable();
    }

    /**
     * Get the table test case instance.
     * 
     * @return TableTestCase
     */
    public function table()
    {
        if (is_null($this->data['table'])) {
            $this->data['table'] = (new Table($this, $this->tableName))->exists();
        }

        return $this->data['table'];
    }

    /**
     * Allow access to some methods as properties.
     * 
     * @return mixed
     */
    public function __get($key)
    {
        if (array_key_exists($key, $this->data)) {
            return $this->getKey($key);
        } elseif (property_exists($this, $key)) {
            return $this->{$key};
        }
    }

    /**
     * Get the applications bootstrap file path.
     * 
     * @return string
     */
    protected function getBootstrapFilePath()
    {
        return __DIR__.'/../../../bootstrap/app.php';
    }

    /**
     * Get a data item by key.
     * 
     * @param  string $key
     * @return mixed
     */
    protected function getKey($key)
    {
        if (method_exists($this, $key)) {
            return $this->$key();
        }

        return $this->data[$key];
    }

    /**
     * Set the model's database table.
     * 
     * @return void
     */
    protected function setTable()
    {
        if (!property_exists($this, 'tableName')) {
            $this->tableName = $this->subject->getTable();
        }
    }
}
