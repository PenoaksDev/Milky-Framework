<?php namespace Milky\Database;


use Milky\Database\Schema\MySqlBuilder;

use Milky\Database\Query\Processors\MySqlProcessor;
use Doctrine\DBAL\Driver\PDOMySql\Driver as DoctrineDriver;

use Milky\Database\Query\Grammars\MySqlGrammar as QueryGrammar;

use Milky\Database\Schema\Grammars\MySqlGrammar as SchemaGrammar;

class MySqlConnection extends Connection
{
	/**
	 * Get a schema builder instance for the connection.
	 *
	 * @return MySqlBuilder
	 */
	public function getSchemaBuilder()
	{
		if ( is_null( $this->schemaGrammar ) )
		{
			$this->useDefaultSchemaGrammar();
		}

		return new MySqlBuilder( $this );
	}

	/**
	 * Get the default query grammar instance.
	 *
	 * @return MySqlGrammar
	 */
	protected function getDefaultQueryGrammar()
	{
		return $this->withTablePrefix( new QueryGrammar );
	}

	/**
	 * Get the default schema grammar instance.
	 *
	 * @return MySqlGrammar
	 */
	protected function getDefaultSchemaGrammar()
	{
		return $this->withTablePrefix( new SchemaGrammar );
	}

	/**
	 * Get the default post processor instance.
	 *
	 * @return MySqlProcessor
	 */
	protected function getDefaultPostProcessor()
	{
		return new MySqlProcessor;
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
