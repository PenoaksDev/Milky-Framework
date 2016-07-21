<?php namespace Milky\Database;


use Milky\Database\Query\Processors\SQLiteProcessor;
use Doctrine\DBAL\Driver\PDOSqlite\Driver as DoctrineDriver;

use Milky\Database\Query\Grammars\SQLiteGrammar as QueryGrammar;

use Milky\Database\Schema\Grammars\SQLiteGrammar as SchemaGrammar;

class SQLiteConnection extends Connection
{
	/**
	 * Get the default query grammar instance.
	 *
	 * @return SQLiteGrammar
	 */
	protected function getDefaultQueryGrammar()
	{
		return $this->withTablePrefix( new QueryGrammar );
	}

	/**
	 * Get the default schema grammar instance.
	 *
	 * @return SQLiteGrammar
	 */
	protected function getDefaultSchemaGrammar()
	{
		return $this->withTablePrefix( new SchemaGrammar );
	}

	/**
	 * Get the default post processor instance.
	 *
	 * @return SQLiteProcessor
	 */
	protected function getDefaultPostProcessor()
	{
		return new SQLiteProcessor;
	}

	/**
	 * Get the Doctrine DBAL driver.
	 *
	 * @return Driver
	 */
	protected function getDoctrineDriver()
	{
		return new DoctrineDriver;
	}
}
