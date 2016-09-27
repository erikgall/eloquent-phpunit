<?php

namespace EGALL\EloquentPHPUnit\Database;

use DB;
use Schema;
use Doctrine\DBAL\Types\Type;
use EGALL\EloquentPHPUnit\Database\Types\JsonbType;

/**
 * Database table test case class.
 *
 * @author Erik Galloway <erik@mybarnapp.com>
 */
class Table
{
    /**
     * The database table instance.
     *
     * @var \Doctrine\DBAL\Schema\Table
     */
    public $table;

    /**
     * The test instance.
     *
     * @var EloquentTestCase
     */
    protected $context;

    /**
     * The database table name.
     *
     * @var string
     */
    protected $name;

    /**
     * Table test case constructor.
     *
     * @param EloquentTestCase $context
     * @param string $name
     */
    public function __construct($context, $name)
    {
        $this->context = $context;
        $this->name = $name;
        $this->addJsonbType();
    }

    /**
     * Get a column test case instance.
     *
     * @param string $column
     * @return \EGALL\EloquentPHPUnit\Database\TableColumnTestCase
     */
    public function column($column)
    {
        return $this->tableColumn($column)->exists();
    }

    /**
     * Assert that the table exists in the database.
     *
     * @return $this
     */
    public function exists()
    {
        $this->context->assertTrue(Schema::hasTable($this->name), "The table {$this->name} does not exist.");

        return $this;
    }

    /**
     * Get the table name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->table->getName();
    }

    /**
     * Assert the table has timestamp columns.
     *
     * @return $this
     */
    public function hasTimestamps()
    {
        $this->column('created_at')->dateTime()->nullable();
        $this->column('updated_at')->dateTime()->nullable();

        return $this;
    }

    /**
     * Get a column's test case instance.
     *
     * @param $column
     * @return \EGALL\EloquentPHPUnit\Database\TableColumnTestCase
     */
    protected function tableColumn($column)
    {
        if (is_null($this->table)) {
            $this->setTable();
        }

        return new Column($this->context, $this->table, $column);
    }

    /**
     * Set the table details instance.
     * 
     * @return void
     */
    protected function setTable()
    {
        $this->table = DB::getDoctrineSchemaManager()->listTableDetails($this->name);
    }

    /**
     * Register the jsonb database column.
     * 
     * @return void
     */
    protected function addJsonbType() {
        if (!Type::hasType('jsonb')) {
            Type::addType('jsonb', JsonbType::class);
            DB::getDoctrineConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('JSONB', 'jsonb');
        }
    }
}
