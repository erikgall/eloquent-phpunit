# Eloquent PHPUnit

#### Test your Laravel Eloquent model's and database schema

This package was written for a project of mine. It was inspired by the Rails testing framework RSpec and how Rails world tests their models and database. So what can you test? You can test the following in your Laravel Eloquent models:

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

The easiest way to install is through composer using the terminal: 
```
composer require erikgall/eloquent-phpunit
```

## Usage

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

- Initial Release: 8/5/2016

## Credits

- [Erik Galloway](https://github.com/erikgall)
- [Laravel Framework](https://laravel.com)

## License

TODO: Write license