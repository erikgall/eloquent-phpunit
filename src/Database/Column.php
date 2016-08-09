<?php

namespace EGALL\EloquentPHPUnit\Database;

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
     * @var TableTestCase
     */
    protected $table;

    /**
     * The column name.
     *
     * @var string
     */
    protected $name;

    /**
     * Table column test case constructor.
     * 
     * @param EloquentTestCase $context
     * @param TableTestCase    $table
     * @param string           $name
     */
    public function __construct($context, $table, $name)
    {
        $this->context = $context;
        $this->table = $table;
        $this->name = $name;
    }

    /**
     * Assert the column is of boolean type.
     * 
     * @return $this
     */
    public function boolean()
    {
        return $this->ofType(BooleanType::class);
    }

    /**
     * Assert the column is of type date.
     * 
     * @return $this
     */
    public function date()
    {
        return $this->ofType(DateType::class);
    }

    /**
     * Assert the column is of type dateTime.
     * 
     * @return $this
     */
    public function dateTime()
    {
        return $this->ofType(DateTimeType::class);
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
        $this->context->assertTrue($this->table->hasForeignKey($name), "The foreign key {$name} does not exist.");

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
        $this->context->assertEquals($value, $this->get('default'), "The default value ({$this->get('default')}) does not equal {$value}");

        return $this;
    }

    /**
     * Test that a column exists.
     * 
     * @return $this
     */
    public function exists()
    {
        $this->context->assertTrue($this->table->hasColumn($this->name), "The table column `{$this->name}` does not exist.");

        return $this;
    }

    /**
     * Assert that the column is auto-incremented.
     *
     * @return $this
     */
    public function increments()
    {
        $this->context->assertTrue($this->get('autoincrement'), "The column {$this->name} does not auto-increment");

        return $this->primary();
    }

    /**
     * Assert that the column is indexed.
     * 
     * @return $this
     */
    public function index()
    {
        $index = $this->getIndexName();

        $this->context->assertTrue(
            $this->table->hasIndex($index), "The {$this->name} column is not indexed."
        );

        $this->context->assertTrue(
            $this->table->getIndex($index)->isSimpleIndex(), "The {$this->name} column is not a simple index."
        );
    }

    /**
     * Assert that the column is of type integer.
     * 
     * @return $this
     */
    public function integer()
    {
        return $this->ofType(IntegerType::class);
    }

    /**
     * Assert that the column is not nullable.
     * 
     * @return $this
     */
    public function notNullable()
    {
        $this->context->assertTrue($this->get('notnull'), "The table column `{$this->name}` is nullable.");

        return $this;
    }
    /**
     * Test that the column is nullable.
     * 
     * @return $this
     */
    public function nullable()
    {
        $this->context->assertFalse($this->get('notnull'), "The table column `{$this->name}` is not nullable.");

        return $this;
    }

    /**
     * Assert a column is of a certain type.
     * 
     * @return $this
     */
    public function ofType($type)
    {
        $this->context->assertInstanceOf($type, $this->get('type'), "The column of type: {$this->get('type')} and not of type {$type}");

        return $this;
    }

    /**
     * Assert that the column is a primary key.
     * 
     * @return $this
     */
    public function primary()
    {
        $noPrimaryMessage = "The table {$this->table->getName()} does not have a primary key.";
        $this->context->assertTrue($this->table->hasPrimaryKey(), $noPrimaryMessage);

        $key = $this->table->getPrimaryKey();

        $this->context->assertTrue(in_array($this->name, $key->getColumns()), "The column {$this->name} is not a primary key.");
        $this->context->assertTrue($key->isPrimary());
        $this->context->assertTrue($key->isUnique());

        return $this;
    }

    /**
     * Assert the column is a string/varchar type.
     * 
     * @return $this
     */
    public function string()
    {
        return $this->ofType(StringType::class);
    }

    /**
     * Assert the column is a text field.
     * 
     * @return $this
     */
    public function text()
    {
        return $this->ofType(TextType::class);
    }

    /**
     * Assert that the column has a unique index.
     * 
     * @return $this
     */
    public function unique()
    {
        $key = $this->getIndexName('unique');

        $notSetMessage = "The unique index {$key} is not an index on the table {$this->table->getName()}";
        $notUniqueMessage = "The column {$this->name} is not unique in the database.";

        $this->context->assertTrue(array_key_exists($key, $this->table->getIndexes()), $notSetMessage);
        $this->context->assertTrue($this->table->getIndexes()[$key]->isUnique(), $notUniqueMessage);
    }

    protected function getIndexName($suffix = 'index')
    {
        return "{$this->table->getName()}_{$this->name}_{$suffix}";
    }
}
