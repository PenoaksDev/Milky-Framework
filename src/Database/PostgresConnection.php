<?php

namespace Penoaks\Database;

use Penoaks\Database\Schema\PostgresBuilder;
use Doctrine\DBAL\Driver\PDOPgSql\Driver as DoctrineDriver;
use Penoaks\Database\Query\Processors\PostgresProcessor;
use Penoaks\Database\Query\Grammars\PostgresGrammar as QueryGrammar;
use Penoaks\Database\Schema\Grammars\PostgresGrammar as SchemaGrammar;

class PostgresConnection extends Connection
{
	/**
	 * Get a schema builder instance for the connection.
	 *
	 * @return \Penoaks\Database\Schema\PostgresBuilder
	 */
	public function getSchemaBuilder()
	{
		if (is_null($this->schemaGrammar))
{
			$this->useDefaultSchemaGrammar();
		}

		return new PostgresBuilder($this);
	}

	/**
	 * Get the default query grammar instance.
	 *
	 * @return \Penoaks\Database\Query\Grammars\PostgresGrammar
	 */
	protected function getDefaultQueryGrammar()
	{
		return $this->withTablePrefix(new QueryGrammar);
	}

	/**
	 * Get the default schema grammar instance.
	 *
	 * @return \Penoaks\Database\Schema\Grammars\PostgresGrammar
	 */
	protected function getDefaultSchemaGrammar()
	{
		return $this->withTablePrefix(new SchemaGrammar);
	}

	/**
	 * Get the default post processor instance.
	 *
	 * @return \Penoaks\Database\Query\Processors\PostgresProcessor
	 */
	protected function getDefaultPostProcessor()
	{
		return new PostgresProcessor;
	}

	/**
	 * Get the Doctrine DBAL driver.
	 *
	 * @return \Doctrine\DBAL\Driver\PDOPgSql\Driver
	 */
	protected function getDoctrineDriver()
	{
		return new DoctrineDriver;
	}
}
