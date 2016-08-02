<?php namespace Milky\Database;

use Milky\Binding\Resolvers\ServiceResolver;
use Milky\Binding\UniversalBuilder;
use Milky\Database\Connectors\ConnectionFactory;
use Milky\Database\Console\Seeds\SeedCommand;
use Milky\Database\Eloquent\Nested\Generators\MigrationGenerator;
use Milky\Database\Eloquent\Nested\Generators\ModelGenerator;
use Milky\Database\Eloquent\Model;
use Milky\Database\Eloquent\Nested\Console\MakeNestedCommand;
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
	protected $mgr;

	public function __construct()
	{
		$this->mgr = new DatabaseManager( new ConnectionFactory() );

		// TODO THIS! -- Framework::set( 'seeder', BindingBuilder::resolveBinding( Seeder::class ) );

		Model::clearBootedModels();
		Model::setConnectionResolver( $this->mgr );

		$this->addClassAlias( DatabaseManager::class, 'db.mgr' );

		$command = UniversalBuilder::getResolver( 'command' );
		$command->seed = new SeedCommand( $this->mgr );
		$command->makeNested = new MakeNestedCommand( new MigrationGenerator( Filesystem::i() ), new ModelGenerator( Filesystem::i() ) );
	}

	public function factory()
	{
		return $this->mgr->factory();
	}

	public function connection()
	{
		return $this->mgr->connection();
	}

	public function key()
	{
		return 'db';
	}
}
