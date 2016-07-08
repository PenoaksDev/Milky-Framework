## Illuminate Database

The Illuminate Database component is a full database toolkit for PHP, providing an expressive query builder, ActiveRecord style ORM, and schema builder. It currently supports MySQL, Postgres, SQL Server, and SQLite. It also serves as the database layer of the Penoaks PHP framework.

### Usage Instructions

First, create a new "Capsule" manager instance. Capsule aims to make configuring the library for usage outside of the Framework framework as easy as possible.

```PHP
use Penoaks\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

$capsule->addConnection([
	'driver'	=> 'mysql',
	'host'	  => 'localhost',
	'database'  => 'database',
	'username'  => 'root',
	'password'  => 'password',
	'charset'   => 'utf8',
	'collation' => 'utf8_unicode_ci',
	'prefix'	=> '',
]);

// Set the event dispatcher used by Eloquent models... (optional)
use Penoaks\Events\Dispatcher;
use Penoaks\Framework;
$capsule->setEventDispatcher(new Dispatcher(new Bindings));

// Make this Capsule instance available globally via static methods... (optional)
$capsule->setAsGlobal();

// Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
$capsule->bootEloquent();
```

> `composer require "illuminate/events"` required when you need to use observers with Eloquent.

Once the Capsule instance has been registered. You may use it like so:

**Using The Query Builder**

```PHP
$users = Capsule::table('users')->where('votes', '>', 100)->get();
```
Other core methods may be accessed directly from the Capsule in the same manner as from the DB facade:
```PHP
$results = Capsule::select('select * from users where id = ?', array(1));
```

**Using The Schema Builder**

```PHP
Capsule::schema()->create('users', function($table)
{
	$table->increments('id');
	$table->string('email')->unique();
	$table->timestamps();
});
```

**Using The Eloquent ORM**

```PHP
class User extends Foundation\Database\Eloquent\Model {}

$users = User::where('votes', '>', 1)->get();
```

For further documentation on using the various database facilities this library provides, consult the [Framework framework documentation](http://framework.com/docs).
