# Eloquent PHPUnit

#### Test your Laravel Eloquent model's and database schema

This package was written for a project of mine. It was inspired by the Rails testing framework RSpec and how Rails world tests their models and database. So what can you test? You can test the following in your Laravel Eloquent models:

## Table of Contents

1. [Installation](#installation)
2. [What can be tested](#what-can-be-tested)
3. [Documentation](#documentation)
	1. [Properties](#test-class-properties)
	2. [Table Testing Methods](#database-testing-methods)
	3. [Model Testing Methods](#model-testing-methods)
4. [Example Model Test Class](#example-model-test)
5. [Contributing](#contributing)
6. [Version Release History](#history)
7. [Projects using Eloquent-PHPUnit](#projects-using-eloquent-phpunit)
8. [Author](#author)
9. [License](#license)

## What can be tested

- Casted attribute array
- Fillable attribute array
- Hidden attribute array
- Dates attribute array
- Relationship methods

You can also test your database tables such as:
- Table exists
- Table column exists
- Table column type (string, text, date, datetime, boolean, etc.).
- Column default value
- Null/Not Null
- Auto-incremented primary keys.
- Table indexes
- Unique indexes
- Foreign Key relationships

## Installation

1. The easiest way to use/install this package is by using composer in your terminal:
```
composer require erikgall/eloquent-phpunit
```
2. Or you can add the following line to your `require-dev` dependencies in your `composer.json` file
```json
{
	"require-dev": {
		"erikgall/eloquent-phpunit": "~1.0"
	}
}
```


## Documentation

### Test Class Properties

| Name | Type | Required | Default  | Description |
|---------------|-------------------------------------|----------|----------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| defaultSeeder | string | false | DatabaseSeeder | The database seeder class name that calls the rest of your seeders (only used if seedDatabase property is not set to false). |
| data | array | false | - | Do not overwrite this property. It is used to store the model's data. You can access this data by calling any of the data array's keys like a class property ($this->fillable, $this->casts, $this->table) |
| model | string | true | - | The FQCN of the eloquent model that is to be tested (ex. App\User) |
| seedDatabase | boolean | false | true | Should the database be seeded before each test. If you are not running tests that require data in the database, you should set this to false to speed up your tests. |
| seeders | array | false | - | If you wish to only call certain seeder classes you can set them here (ex. ['UsersTableSeeder', 'PostsTableSeeder'] (only used if seedDatabase property is not set to false). |
| subject | Model** | false | -  | This is the instance of the model class that is being tested. When setting up a test, the EloquentTestCase class initializes a new empty model. |

**These settings are only used if the seedDatabase property is not set to false (the default value for the seedDatabase property is true).*

** The subject property is an instance of \Illuminate\Database\Eloquent\Model.

### Database Testing Methods

#### \EGALL\EloquentPHPUnit\Database\Table

Get the `EGALL\EloquentPHPUnit\Database\Table` class instance by calling the table property.

**Usage:**

```php
	$this->table
```

#### Table methods
---

##### column($columnName)

Initializes a new `EGALL\EloquentPHPUnit\Database\Column` class instance for table's column name that is passed in.

**Usage:**

```php
	$this->table->column('column_name')
```

Returns: `EGALL\EloquentPHPUnit\Database\Column`

--- 

##### exists()

Assert that the table exists in the database.

**Usage:**

```php
	$this->table->exists();
```

**Returns:** `EGALL\EloquentPHPUnit\Database\Table`

---

##### hasTimestamps()

Assert that the table has timestamp columns.

**Usage:**

```php
	$this->$table->hasTimestamps();
```

**Returns:** `EGALL\EloquentPHPUnit\Database\Table`

--- 

### Model Testing Methods

// TODO

## Example Model Test

```php
Class UserModelTest extends \EGALL\EloquentPHPUnit\EloquentTestCase {
	
	protected $model = 'App\User';

	// If you want to run the DatabaseSeeder class
	protected $seedDatabase = true;

	// If you only want to run a specific seeder
	protected $seeders = ['UsersTableSeeder', 'SchoolsTableSeeder'];

	// Change the default seeder that calls the rest of your seeders.
	// The default is the default Laravel Seeder named: DatabaseSeeder. 
	// Ex. (You have a TestDatabaseSeeder and the default DatabaseSeeder).
	protected $defaultSeeder = 'TestDatabaseSeeder'

	/**
	 * Test the database table.
	 */
	public function testDatabaseTable() {
		$this->table->column('id')->integer()->increments();
		$this->table->column('name')->string()->nullable();
		$this->table->column('email')->string()->notNullable()->unique();
		$this->table->column('password')->string()->notNullable();
		$this->table->column('dob')->date()->nullable();
		$this->table->column('avatar_id')->integer()->foreign('images', 'id', $onUpdate = 'cascade', $onDelete = 'cascade');
		$this->table->column('is_verified')->boolean()->defaults(false);
		$this->table->column('is_admin')->boolean()->defaults(false);
		$this->table->column('verification_sent_at')->dateTime()->nullable();
		$this->table->column('invite_sent_at')->dateTime()->nullable();
		$this->table->column('api_token')->string()->index();
		$this->table->column('remember_token')->string()->nullable();
		$this->table->hasTimestamps();
	}

	/**
	 * Test the model's properties.
	 */
	public function testModelProperties() {
		$this->hasFillable('name', 'email', 'password', 'dob', 'avatar_id')
			 ->hasHidden('password', 'remember_token')
			 ->hasCasts('is_verified', 'boolean') 
			 // or
			 ->hasCasts(['is_verified' => 'boolean', 'is_admin' => 'boolean'])
			 ->hasDates('verification_sent_at', 'invite_sent_at')
			 ->belongsTo(Image::class) 
			 // if method name = 'image()' or
			 ->belongsTo(Image::class, $customMethod = 'avatar')
			 ->hasMany(Profile::class)
			 ->morphsTo($method = 'slug', $morphTo = 'sluggable') 
			 // or: example below assumes the db fields are: 'sluggable_id' and 'sluggable_type'
			 ->morphsTo('sluggable'); 
	}
}
```

## Contributing

1. Fork it.
2. Create your branch: `git checkout -b my-new-feature`
3. Commit your changes: `git commit -am 'Add some feature'`
4. Push to the branch: `git push origin my-new-feature`
5. Submit a pull request.

## History

- v1.0.0 Released: 8/5/2016
- v1.0.3 Released: 8/9/2016
- v1.0.6 Released: 9/21/2016

## Projects using Eloquent-PHPUnit

- [Canvas ★765](https://github.com/austintoddj/canvas): A minimal blogging application built on top of Laravel 5 by [@austintoddj](https://github.com/austintoddj)

## Author

- [Erik Galloway](https://github.com/erikgall)


## License

Eloquent-PHPUnit is an open-sourced software licensed under the [MIT license](https://github.com/erikgall/eloquent-phpunit/blob/master/LICENSE).