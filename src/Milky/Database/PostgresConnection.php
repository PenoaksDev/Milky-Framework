<?php namespace Milky\Database;


use Milky\Database\Schema\PostgresBuilder;
use Doctrine\DBAL\Driver\PDOPgSql\Driver as DoctrineDriver;

use Milky\Database\Query\Processors\PostgresProcessor;

use Milky\Database\Query\Grammars\PostgresGrammar as QueryGrammar;

use Milky\Database\Schema\Grammars\PostgresGrammar as SchemaGrammar;

class PostgresConnection extends Connection
{
	/**
	 * Get a schema builder instance for the connection.
	 *
	 * @return PostgresBuilder
	 */
	public function getSchemaBuilder()
	{
		if ( is_null( $this->schemaGrammar ) )
		{
			$this->useDefaultSchemaGrammar();
		}

		return new PostgresBuilder( $this );
	}

	/**
	 * Get the default query grammar instance.
	 *
	 * @return PostgresGrammar
	 */
	protected function getDefaultQueryGrammar()
	{
		return $this->withTablePrefix( new QueryGrammar );
	}

	/**
	 * Get the default schema grammar instance.
	 *
	 * @return PostgresGrammar
	 */
	protected function getDefaultSchemaGrammar()
	{
		return $this->withTablePrefix( new SchemaGrammar );
	}

	/**
	 * Get the default post processor instance.
	 *
	 * @return PostgresProcessor
	 */
	protected function getDefaultPostProcessor()
	{
		return new PostgresProcessor;
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
