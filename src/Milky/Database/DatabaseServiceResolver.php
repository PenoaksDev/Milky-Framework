<?php namespace Milky\Database;

use Milky\Binding\ServiceResolver;
use Milky\Binding\UniversalBuilder;
use Milky\Database\Connectors\ConnectionFactory;
use Milky\Database\Console\Seeds\SeedCommand;
use Milky\Database\Eloquent\Model;
use Milky\Database\Eloquent\Nested\Console\MakeNestedCommand;
use Milky\Database\Eloquent\Nested\Generators\MigrationGenerator;
use Milky\Database\Eloquent\Nested\Generators\ModelGenerator;
use Milky\Filesystem\Filesystem;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class DatabaseServiceResolver extends ServiceResolver
{
	/**
	 * @var DatabaseManager
	 */
	protected $mgrInstance;

	public function __construct()
	{
		// TODO THIS! -- Framework::set( 'seeder', BindingBuilder::resolveBinding( Seeder::class ) );

		$this->addClassAlias( DatabaseManager::class, 'mgr' );
		$this->setDefault( 'mgr' );
	}

	/**
	 * @return DatabaseManager
	 */
	public function mgr()
	{
		if ( is_null( $this->mgrInstance ) )
		{
			$this->mgrInstance = new DatabaseManager( new ConnectionFactory() );

			Model::clearBootedModels();
			Model::setConnectionResolver( $this->mgrInstance );

			$command = UniversalBuilder::getResolver( 'command' );
			$command->seed = new SeedCommand( $this->mgrInstance );
			$command->makeNested = new MakeNestedCommand( new MigrationGenerator( Filesystem::i() ), new ModelGenerator( Filesystem::i() ) );
		}

		return $this->mgrInstance;
	}

	public function factory()
	{
		return $this->mgr()->factory();
	}

	public function connection()
	{
		return $this->mgr()->connection();
	}

	public function key()
	{
		return 'db';
	}
}
