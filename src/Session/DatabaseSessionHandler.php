<?php

namespace Penoaks\Session;

use Carbon\Carbon;
use SessionHandlerInterface;
use Penoaks\Contracts\Auth\Guard;
use Penoaks\Database\ConnectionInterface;
use Penoaks\Framework;

class DatabaseSessionHandler implements SessionHandlerInterface, ExistenceAwareInterface
{
	/**
	 * The database connection instance.
	 *
	 * @var \Penoaks\Database\ConnectionInterface
	 */
	protected $connection;

	/**
	 * The name of the session table.
	 *
	 * @var string
	 */
	protected $table;

	/*
	 * The number of minutes the session should be valid.
	 *
	 * @var int
	 */
	protected $minutes;

	/**
	 * The bindings instance.
	 *
	 * @var \Penoaks\Framework
	 */
	protected $bindings;

	/**
	 * The existence state of the session.
	 *
	 * @var bool
	 */
	protected $exists;

	/**
	 * Create a new database session handler instance.
	 *
	 * @param  \Penoaks\Database\ConnectionInterface  $connection
	 * @param  string  $table
	 * @param  string  $minutes
	 * @param  \Penoaks\Framework|null  $bindings
	 * @return void
	 */
	public function __construct(ConnectionInterface $connection, $table, $minutes, Bindings $bindings = null)
	{
		$this->table = $table;
		$this->minutes = $minutes;
		$this->bindings = $bindings;
		$this->connection = $connection;
	}

	/**
	 * {@inheritdoc}
	 */
	public function open($savePath, $sessionName)
	{
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function close()
	{
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function read($sessionId)
	{
		$session = (object) $this->getQuery()->find($sessionId);

		if (isset($session->last_activity))
{
			if ($session->last_activity < Carbon::now()->subMinutes($this->minutes)->getTimestamp())
{
				$this->exists = true;

				return;
			}
		}

		if (isset($session->payload))
{
			$this->exists = true;

			return base64_decode($session->payload);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function write($sessionId, $data)
	{
		$payload = $this->getDefaultPayload($data);

		if (! $this->exists)
{
			$this->read($sessionId);
		}

		if ($this->exists)
{
			$this->getQuery()->where('id', $sessionId)->update($payload);
		}
else
{
			$payload['id'] = $sessionId;

			$this->getQuery()->insert($payload);
		}

		$this->exists = true;
	}

	/**
	 * Get the default payload for the session.
	 *
	 * @param  string  $data
	 * @return array
	 */
	protected function getDefaultPayload($data)
	{
		$payload = ['payload' => base64_encode($data), 'last_activity' => time()];

		if (! $bindings = $this->bindings)
{
			return $payload;
		}

		if ($bindings->bound(Guard::class))
{
			$payload['user_id'] = $bindings->make(Guard::class)->id();
		}

		if ($bindings->bound('request'))
{
			$payload['ip_address'] = $bindings->make('request')->ip();

			$payload['user_agent'] = substr(
				(string) $bindings->make('request')->header('User-Agent'), 0, 500
			);
		}

		return $payload;
	}

	/**
	 * {@inheritdoc}
	 */
	public function destroy($sessionId)
	{
		$this->getQuery()->where('id', $sessionId)->delete();
	}

	/**
	 * {@inheritdoc}
	 */
	public function gc($lifetime)
	{
		$this->getQuery()->where('last_activity', '<=', time() - $lifetime)->delete();
	}

	/**
	 * Get a fresh query builder instance for the table.
	 *
	 * @return \Penoaks\Database\Query\Builder
	 */
	protected function getQuery()
	{
		return $this->connection->table($this->table);
	}

	/**
	 * Set the existence state for the session.
	 *
	 * @param  bool  $value
	 * @return $this
	 */
	public function setExists($value)
	{
		$this->exists = $value;

		return $this;
	}
}
