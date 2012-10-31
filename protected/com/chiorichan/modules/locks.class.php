<?
	/**
	 * (C) 2012 Chiori Greene
	 * All Rights Reserved.
	 * Author: Chiori Greene
	 * E-Mail: chiorigreene@gmail.com
	 * 
	 * This class is intellectual property of Chiori Greene and can only be distributed in whole with its parent
	 * framework which is known as Chiori Framework.
	 * 
	 * Keep software like this free and open source by following the authors wishes.
	 * 
	 * Class Name: Chiori Locks
	 * Version: 1.0.0 Offical Release
	 * Released: June 30th, 2012
	 * Description: This class is used to give scripts easy access to running in a single instance mode.
	 * Note: It is the responsability of the scripts to properly call class. Misuse will lead to undesired results. Read the Documentation.
	 */

class com_chiorichan_modules_locks
{
	public $chiori;
	private $locksPath;

	function __construct ($parentClass)
	{
		$this->chiori = $parentClass;
		$this->locksPath = __FW__ . "/locks/";
	}
	
	/*
	 * This function gives scripts a complete all in one call for single instance mode.
	 * $wait => If lock exists wait for exit else return false?
	 * $continueOnAbort => If client disconnects connection will the script continue? ($timeOut must not be disabled. i.e. -1)
	 * $timeOut => What is the maximum execution time? -1 = Disabled, 0 = System Default
	 * $waitTimeOut => If $wait is true then this var will define when to give up
	 */
	function fullService ($lockName = "myLock001", $wait = true, $continueOnAbort = true, $timeOut = 0, $waitTimeOut = 60)
	{
		$this->chiori->Debug1("LOCKS: Attempting to lock single instance mode for user.");
		
		/* If lock exists wait or quit */
		$lt = 0;
		while ($this->check($lockName) && $wait)
		{
			sleep(1);
			$lt++;
			
			$this->chiori->Debug3("LOCKS: Found " . $lockName . " was already registered, so waiting. Elapsed " . $lt . " seconds of " . $waitTimeOut . " timeout.");
			
			if ($lt > $waitTimeOut)
				return false;
		}
		
		/* Check if we got here because wait had timed out */
		if ($this->check($lockName) && !$wait)
		{
			$this->chiori->Debug1("LOCKS: Attempt to lock single instance mode failed because either waitTimeout had elapsed or waiting is disabled.");
			return false;
		}
		
		/* Create new lock */
		if (!$this->create($lockName, $timeOut, false))
		{
			$this->chiori->Debug1("LOCKS: Lock was successfully created.");
			return false;
		}
		
		if ($continueOnAbort)
		{
			/* Timeout can not be disabled when "$continueOnAbort" is set to true. */
			if ($timeOut < 0)
				return false;
			
			/* Tell apache to not kill process just because client disconnected. */
			ignore_user_abort(true);
		}

		if ($timeOut > 0)
		{
			/* Set user defined execution limit */
			set_time_limit($timeOut);
		}
		elseif ($timeOut < 0)
		{
			/* Disable execution limit */
			set_time_limit(0);
		}
		
		return true;
	}
	
	function create ($lockName, $timeOut = 3600, $overWrite = false)
	{
		if ($overWrite || !$this->check($lockName))
		{
			file_put_contents($this->locksPath . $lockName . ".lck", (microtime(true) + $timeOut) . "\n" . getmypid());
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/* Returns true if lock exists and is not expired. */
	function check ($lockName)
	{
		$lockFile = $this->locksPath . $lockName . ".lck";
		
		/* Does lock file exist? */
		if (!file_exists($lockFile))
			return false;
		
		/* Retrieve lock timeout and pid */
		$result = file_get_contents($lockFile);
		list ($timeout, $pid) = explode("\n", $result);
		
		/* Is process running? */
		exec("ps aux | grep " . $pid . " | grep -v grep", $pids);
		if (empty($pids))
		{
			unlink ($lockFile);
			return false;
		}
		
		/* Has timeout passed? */
		if ($timeout < microtime(true))
		{
			exec("kill -9 " . $pid);
			unlink ($lockFile);
			return false;
		}
		
		return true;
	}
	
	function destroy ($lockName)
	{
		$lockFile = $this->locksPath . $lockName . ".lck";
		unlink ($lockFile);
	}
}
?>