<?php

namespace EGALL\EloquentPHPUnit\Database;

use DB;
use Doctrine\DBAL\Types\DateType;
use Doctrine\DBAL\Types\TextType;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\BooleanType;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\DateTimeType;

/**
 * Database table column test case.
 * 
 * @author Erik Galloway <erik@mybarnapp.com>
 */
class Column
{
    /**
     * The test instance.
     *
     * @var EloquentTestCase
     */
    protected $context;

    /**
     * The column's database data.
     *
     * @var array
     */
    protected $data;

    /**
     * The table test case instance.
     *
     * @var \Doctrine\DBAL\Schema\Table
     */
    protected $table;

    /**
     * The column name.
     *
     * @var string
     */
    protected $name;

    /**
     * The column types array.
     *
     * @var array
     */
    protected $types = [
        'boolean'  => BooleanType::class,
        'date'     => DateType::class,
        'dateTime' => DateTimeType::class,
        'integer'  => IntegerType::class,
        'string'   => StringType::class,
        'text'     => TextType::class,
    ];

    /**
     * Table column test case constructor.
     * 
     * @param \EGALL\EloquentPHPUnit\EloquentTestCase $context
     * @param \Doctrine\DBAL\Schema\Table $table
     * @param string $name
     */
    public function __construct($context, $table, $name)
    {
        $this->context = $context;
        $this->table = $table;
        $this->name = $name;
    }

    /**
     * Assert that the table has a foreign key relationship.
     * 
     * @param  string $table
     * @param  string $column
     * @param  string $onUpdate
     * @param  string $onDelete
     * @return $this
     */
    public function foreign($table, $column = 'id', $onUpdate = 'cascade', $onDelete = 'cascade')
    {
        if (DB::connection() instanceof \Illuminate\Database\SQLiteConnection) {
            $this->context->markTestIncomplete('Foreign keys cannot be tested with a SQLite database.');

            return $this;
        }

        $name = $this->getIndexName('foreign');
        $this->assertTrue($this->table->hasForeignKey($name), "The foreign key {$name} does not exist.");

        $key = $this->table->getForeignKey($name);
        $onUpdate && $this->context->assertEquals(strtoupper($onUpdate), $key->onUpdate());
        $onDelete && $this->context->assertEquals(strtoupper($onDelete), $key->onDelete());

        $this->context->assertEquals($table, $key->getForeignTableName());
        $this->context->assertContains($column, $key->getForeignColumns());

        return $this;
    }

    /**
     * Get a data key by name.
     * 
     * @param  string $key
     * @return mixed
     */
    public function get($key)
    {
        if (is_null($this->data)) {
            $this->data = $this->table->getColumn($this->name)->toArray();
        }

        return $this->data[$key];
    }

    /**
     * Test the default value.
     * 
     * @param  mixed
     * @return $this
     */
    public function defaults($value)
    {
        $this->context->assertEquals(
            $value, $this->get('default'), "The default value ({$this->get('default')}) does not equal {$value}"
        );

        return $this;
    }

    /**
     * Test that a column exists.
     * 
     * @return $this
     */
    public function exists()
    {
        $this->assertTrue(
            $this->table->hasColumn($this->name), "The table column `{$this->name}` does not exist."
        );

        return $this;
    }

    /**
     * Assert that the column is auto-incremented.
     *
     * @return $this
     */
    public function increments()
    {
        $message = "The column {$this->name} is not auto-incremented.";

        return $this->integer()->assertTrue($this->get('autoincrement'), $message)->primary();
    }

    /**
     * Assert that the column is indexed.
     * 
     * @return $this
     */
    public function index()
    {
        $index = $this->getIndexName();

        $this->assertTrue(
            $this->table->hasIndex($index), "The {$this->name} column is not indexed."
        );

        $this->assertTrue(
            $this->table->getIndex($index)->isSimpleIndex(), "The {$this->name} column is not a simple index."
        );
    }

    /**
     * Assert a column is of a certain type.
     * 
     * @return $this
     */
    public function ofType($type)
    {
        $this->context->assertInstanceOf(
            $type, $this->get('type'), "The {$this->name} column is not of type {$type}"
        );

        return $this;
    }

    /**
     * Assert that the column is a primary key.
     * 
     * @return $this
     */
    public function primary()
    {
        $key = $this->tableHasPrimary()->getPrimaryKey();
        $message = "The column {$this->name} is not a primary key.";

        return $this->assertTrue(in_array($this->name, $key->getColumns()), $message)
                    ->assertTrue($key->isPrimary())
                    ->assertTrue($key->isUnique());
    }

    /**
     * Assert that the column has a unique index.
     * 
     * @return $this
     */
    public function unique()
    {
        $this->assertUniqueIndex($this->getIndexName('unique'), $this->table->getIndexes());
    }

    public function __call($method, $args)
    {
        if (method_exists($this, $method)) {
            return $this->$method($args);
        }

        return $this->assertColumn($method, $args);
    }

    /**
     * Assert the column is nullable.
     *
     * @param bool $negate
     * @return $this
     */
    protected function assertNullable($negate = false)
    {
        if ($negate) {
            return $this->assertTrue($this->get('notnull'),  "The table column `{$this->name}` is nullable");
        }

        return $this->assertFalse($this->get('notnull'),  "The table column `{$this->name}` is not nullable");
    }

    /**
     * Call a column assertion method.
     *
     * @param string $method
     * @param array $args
     * @return $this
     */
    protected function assertColumn($method, $args)
    {
        if (array_key_exists($method, $this->types)) {
            return $this->ofType($this->types[$method]);
        }

        if (str_contains($method, ['default', 'Default'])) {
            return $this->defaults($args[0]);
        }

        if (str_contains($method, ['null', 'Null'])) {
            return $this->assertNullable(str_contains($method, 'not'));
        }

        throw new \Exception("The database table column assertion {$method} does not exist.");
    }

    /**
     * Assert a condition is false alias.
     *
     * @param bool $condition
     * @param string|null $message
     * @return $this
     */
    protected function assertFalse($condition, $message = null)
    {
        $this->context->assertFalse($condition, $message);

        return $this;
    }

    /**
     * Assert a condition is true alias.
     *
     * @param bool $condition
     * @param string|null $message
     * @return $this
     */
    protected function assertTrue($condition, $message = null)
    {
        $this->context->assertTrue($condition, $message);

        return $this;
    }

    /**
     * Assert a key is a unique index.
     *
     * @param string $key
     * @param array $indexes
     * @return void
     */
    protected function assertUniqueIndex($key, $indexes)
    {
        $this->context->assertArrayHasKey($key, $indexes, "The {$this->name} column is not indexed.");
        $this->assertTrue($indexes[$key]->isUnique(), "The {$this->name} is not a unique index.");
    }

    /**
     * Get the column's index key/name.
     *
     * @param string $suffix
     * @return string
     */
    protected function getIndexName($suffix = 'index')
    {
        return "{$this->table->getName()}_{$this->name}_{$suffix}";
    }

    /**
     * Assert the table has a primary key.
     *
     * @param \Doctrine\DBAL\Schema\Table $table
     * @return $this
     */
    protected function tableHasPrimary()
    {
        $this->assertTrue(
            $this->table->hasPrimaryKey(), "The table {$this->table->getName()} does not have a primary key."
        );

        return $this->table;
    }
}
