<?php

namespace Foundation\Database;

use Foundation\Database\Schema\MySqlBuilder;
use Foundation\Database\Query\Processors\MySqlProcessor;
use Doctrine\DBAL\Driver\PDOMySql\Driver as DoctrineDriver;
use Foundation\Database\Query\Grammars\MySqlGrammar as QueryGrammar;
use Foundation\Database\Schema\Grammars\MySqlGrammar as SchemaGrammar;

class MySqlConnection extends Connection
{
	/**
	 * Get a schema builder instance for the connection.
	 *
	 * @return \Foundation\Database\Schema\MySqlBuilder
	 */
	public function getSchemaBuilder()
	{
		if (is_null($this->schemaGrammar)) {
			$this->useDefaultSchemaGrammar();
		}

		return new MySqlBuilder($this);
	}

	/**
	 * Get the default query grammar instance.
	 *
	 * @return \Foundation\Database\Query\Grammars\MySqlGrammar
	 */
	protected function getDefaultQueryGrammar()
	{
		return $this->withTablePrefix(new QueryGrammar);
	}

	/**
	 * Get the default schema grammar instance.
	 *
	 * @return \Foundation\Database\Schema\Grammars\MySqlGrammar
	 */
	protected function getDefaultSchemaGrammar()
	{
		return $this->withTablePrefix(new SchemaGrammar);
	}

	/**
	 * Get the default post processor instance.
	 *
	 * @return \Foundation\Database\Query\Processors\MySqlProcessor
	 */
	protected function getDefaultPostProcessor()
	{
		return new MySqlProcessor;
	}

	/**
	 * Get the Doctrine DBAL driver.
	 *
	 * @return \Doctrine\DBAL\Driver\PDOMySql\Driver
	 */
	protected function getDoctrineDriver()
	{
		return new DoctrineDriver;
	}
}
