<?php
/*
<!-- $Header: d:\cvs/3.7/Auto-backup/includes/mysqlbackup.php,v 3.2 2008/01/06 15:32:44 pem Exp $ -->

Auto-Backup (Lite) for vBulletin 3.7 - Paul M - v 3.7.001
This lite version is adapted from the original 3.0 version by Trigunflame.
*/

define ('NO_ERRORS',1);
define ('DB_OPTIONS',10000);
define ('DUMP_ERROR',20000);

Class errorHandler
{
	/* --------- Set Message/Error Variables -------- */

	var $STATUS;
	var $MESSAGES;
	
	/* -------- Fill Message/Error Variables -------- */

	// Load Codes
	function loadCodes()
	{
		$this->MESSAGES = array(
			/* No Errors Have Been Encountered */
			NO_ERRORS 		=> 'The Backup was completed successfully.',

			/* Options Regarding the Database Options */
			DB_OPTIONS 		=> 'Could not fetch vbulletin options to close the forum, backup aborted.',

			/* Errors Relating To MYSQL Dumping Errors */
			DUMP_ERROR 		=> 'An error occured during the Backup : $details',
		);
	}

	
	/* ----------- Custom Error Handler ------------ */

	// Error Handler
	function throwError($name,$details=false)
	{
		// Globalize
		global $nextitem;

		// Set Status
		eval("\$this->STATUS = \"".$this->MESSAGES[$name]."\";");

		// Console Output
		echo $this->STATUS;

		// Return to Log
		log_cron_action($this->STATUS, $nextitem);

		// Exit
		exit;
	}
}

Class mysqlBackup Extends errorHandler
{
	/* ------------ DB Object References ------------ */

	// MySQL Database Object
	var $MYSQL;

	/* ----------- Static Class Variables ----------- */

	/* Forum Shutdown Configuration */
	var $SHUTDOWN = 0;
	var $PREVIOUS;
	var $MESSAGE;

	/* Backup Dump Info */
	var $DATE;
	var $DUMP_PATH;

	/* Backup Method */
	var $LOCK = 0;
	var $REPAIR = 0;
	
	/* Backup Type */
	var $TYPE = 2;
	var $TABLES;
	var $COMBINE = 1;

	/* Backup Optimizations */
	var $INNODB = 0;

	/* Command */
	var $COMMAND;


	/* ----------- Dynamic Class Variables ----------- */

	/* Server Information */
	var $OPTIONS;

	/* Dump Full Path */
	var $FULL_PATH;

	/* Dump Short Path */
	var $SHORT_PATH;

	/* Error Out String */
	var $ERROR_OUT;


	/* ----------- Error Function Overload ---------- */ 

	// Error Handler
	function throwError($name,$details=false)
	{
		if ($this->SHUTDOWN) { $this->forumStatus(1); }
		parent::throwError($name,($details ? $details : 0));
	}



	/* -------------- Set Forum Status ------------- */ 

	// Change Forum Status
	function forumStatus($status=1)
	{
		// Status
		if (!$status)
		{
			// Set Previous Message
			$this->PREVIOUS = $this->OPTIONS['bbclosedreason'];

			// Set Closed
			$this->OPTIONS['bbactive'] = '0';
			$this->OPTIONS['bbclosedreason'] = $this->MESSAGE;
		}
		else
		{
			// Set Closed
			$this->OPTIONS['bbactive'] = '1';
			$this->OPTIONS['bbclosedreason'] = ($this->PREVIOUS ? $this->PREVIOUS : '');
		}

		// Update DataStore
		build_datastore('options', serialize($this->OPTIONS),1);
	}


	/* ------------- Misc Class Methods ------------- */ 

	// Create Selected File
	function createFile($file)
	{
		// Check Existance
		if (file_exists($file)) { return; }

		// Try Creation
		@fclose(@fopen($file,'w+'));
		@chmod($file, 0666);

		// Clear Cache
		clearstatcache();

		// Check Again
		if (file_exists($file)) { return; }

		// Command Usage
		switch ($this->COMMAND)
		{
			case 'exec': 
				@exec("echo > ".$file,$o,$e);
				@exec("chmod 0666 ".$file,$o,$e);
			break;
				case 'system': 
				@system("echo > ".$file,$e);
				@system("chmod 0666 ".$file,$e);
			break;
				case 'passthru': 
				@passthru("echo > ".$file,$e);
				@passthru("chmod 0666 ".$file,$e);
			break;
		}
	}

	// Remove Selected File
	function removeFile($file)
	{
		// Check Existance
		if (!file_exists($file)) { return; }

		// Try Removing
		@unlink($file);

		// Clear Cache
		clearstatcache();

		// Check Again
		if (!file_exists($file)) { return; }

		// Command Usage
		switch ($this->COMMAND)
		{
			case 'exec': @exec("rm -f ".$file,$o,$e); break;
			case 'system': @system("rm -f ".$file,$e); break;
			case 'passthru': @passthru("rm -f ".$file,$e); break;
		}
	}

	// Recursively Remove Directory
	function removeDir($directory)
	{
		// Delete File
		if (is_file($directory)) { $this->removeFile($directory); }
 
		// Open Directory?
		if (!$dir = @dir($directory)) { return; }

		// Loop
		while (false !== $file = $dir->read())
		{
			if ($file == '.' || $file == '..') { continue; }
			$this->removeDir("$directory/$file");
		}
 
		// Finish
		$dir->close();
		@rmdir($directory);

		// Clear Cache
		clearstatcache();

		// Check Again
		if (!is_dir($directory)) { return; }

		// Command Usage
		switch ($this->COMMAND)
		{
			case 'exec': @exec("rm -rf ".$directory,$o,$e); break;
			case 'system': @system("rm -rf ".$directory,$e); break;
			case 'passthru': @passthru("rm -rf ".$directory,$e); break;
		}
	}

	// Create Directory
	function createDir($directory)
	{
		// Check Existance
		if (is_dir($directory)) { return; }

		// Make Directory
		$mask = umask(0); 
		@mkdir($directory, 0777);
		umask($mask);

		// Clear Cache
		clearstatcache();

		// Check Again
		if (is_dir($directory)) { return; }

		// Command Usage
		switch ($this->COMMAND)
		{
			case 'exec': @exec("mkdir -m 0777 ".$directory,$o,$e); break;
			case 'system': @system("mkdir -m 0777 ".$directory,$e); break;
			case 'passthru': @passthru("mkdir -m 0777 ".$directory,$e); break;
		}
	}

	// Repair & Optimize Tables
	function repairTables()
	{
		// Predefined Variables
		$tables  = array();
		$lock    = 'LOCK TABLES';
		$unlock  = 'UNLOCK TABLES;';

		// Get Table List
		$result = $this->MYSQL->query('SHOW tables');

		// Store Table List
		while ($table = $this->MYSQL->fetch_array($result,DBARRAY_NUM))
		{
			$tables[] = $table[0];
			$lock .= ' `'.$table[0].'` WRITE,';
		}

		// Remove Ending of LockList
		$lock = rtrim($lock,",").';';

		// Lock Tables
		if ($this->LOCK)
		{
			$this->MYSQL->query($lock);
		}

		// Loop Tables
		foreach ($tables AS $table)
		{
			$error = 0;
			$optimize = 1;

			// Check Table
			$check = $this->MYSQL->query("CHECK TABLE `$table`");
			while ($status = $this->MYSQL->fetch_array($check,DBARRAY_NUM))
			{
				// Status
				if ($status[2] == 'error')
				{
					if ($status[3] == 'The handler for the table doesn\'t support check/repair')
					{
						$optimize = 0;
					}
					else
					{
						$error = 1;
					}
				}
			}

			// Check Table Error
			if ($error)
			{
				// Repair Table
				$repair = $this->MYSQL->query_first("REPAIR TABLE `$table`");

				// Status
				if ($repair[3] != 'OK')
				{
					$error2 = 1;
				}
				else
				{
					$error2 = 0;
					$error = 0;
				}
			}

			// Check Optimize
			if (!$error && !$error2 && $optimize)
			{
				// Optimize Table
				$optimize = $this->MYSQL->query("OPTIMIZE TABLE `$table`");
				while ($status = $this->MYSQL->fetch_array($optimize,DBARRAY_NUM))
				{
					// Status
					if ($status[2] == 'error')
					{
						$error = 1;
					}
				}
			}
		}

		// Unlock Tables
		if ($this->LOCK)
		{
			$this->MYSQL->query($unlock);
		}
	}

	/* ------------- Core Backup Method ------------ */

	// PHP Based Dump
	function phpDump()
	{
		/* Entire Table Dump */
		if ($this->TYPE)
		{
			// Predefined Variables
			$tables  = array();
			$lock    = 'LOCK TABLES';
			$unlock  = 'UNLOCK TABLES;';

			// Store Table List
			foreach ($this->TABLES AS $table)
			{
				$tables[] = $table;
				$lock .= ' `'.$table.'` READ,';
			}

			// Remove Ending of LockList
			$lock = rtrim($lock,",").';';

			// Lock Tables
			if ($this->LOCK)
			{
				$this->MYSQL->query($lock);
			}

			if ($this->COMBINE)
			{
				$this->FILE = $this->FULL_PATH.'-full-backup.sql';
				$this->createFile($this->FILE);
			}

			// Start Parsing Rows
			foreach ($tables AS $table)
			{
				// Create a New File
				if (!$this->COMBINE)
				{
					$this->FILE = $this->FULL_PATH.'-'.$table.'.sql';
					$this->createFile($this->FILE);
				}

				// Open Output
				if (!$output = @fopen($this->FILE,'a'))
				{
					$this->throwError(DUMP_ERROR,"Could not open Destination SQL file for writing.");
				}

				// Set Write Buffer
				@stream_set_write_buffer($output, 0);

				// InnoDb Optimization
				if ($this->INNODB)
				{
					// Construct AutoCommit Off
					fwrite($output,"SET AUTOCOMMIT = 0;\n");

					// Construct Foreign Key Checks Off
					fwrite($output,"SET FOREIGN_KEY_CHECKS = 0;\n\n\n");
				}

				// Create Header
				$tableheader = $this->MYSQL->query_first("SHOW CREATE TABLE `$table`");
				$tableheader = "DROP TABLE IF EXISTS `$table`;\n".$tableheader['Create Table'].";\n\n";

				// Write Header
				fwrite($output,$tableheader);

				// Get Total Rows
				$total = $this->MYSQL->query_first("SELECT COUNT(*) AS count FROM `$table`");
				echo "Processing ".$table." : Total Rows = ".$total['count']."<br />"; vbflush();

				// Check Total & Skip
				if (intval($total['count']) == 0) { continue; }

				// Get Row (Unbuffered)
				$rows = $this->MYSQL->query_read("SELECT * FROM `$table`", false);

				// Fields
				$fields = $this->MYSQL->num_fields($rows);

				// MySQL4+ Optimizations, Construct Disable Keys
				fwrite($output,"/*!40000 ALTER TABLE `$table` DISABLE KEYS */;\n");

				// Get Data
				$r = 0;
				while ($row = $this->MYSQL->fetch_array($rows,DBARRAY_NUM))
				{
					$values = array();
					for ($i=0;$i<$fields;$i++)
					{
						// Check Data
						if (!isset($row[$i]) || is_null($row[$i]))
						{
                    					$values[] = 'NULL';
						}
						else
						{
							$values[] = "'".$this->MYSQL->escape_string($row[$i])."'";
						}
					}
					$r++;
	
					// Construct Insert
					fwrite($output,"INSERT INTO `$table` VALUES (".implode(',',$values).");\n");
				}

				// MySQL4+ Optimizations, Construct Enable Keys
				fwrite($output,"/*!40000 ALTER TABLE `$table` ENABLE KEYS */;\n\n");

				// InnoDb Optimization
				if ($this->INNODB)
				{
					// Construct AutoCommit On
					fwrite($output,"\n"."SET AUTOCOMMIT = 1;\n");

					// Construct Commit
					fwrite($output,"COMMIT;\n");

					// Construct Foreign Key Checks On
					fwrite($output,"SET FOREIGN_KEY_CHECKS = 1;\n\n\n");
				}

				// Close Output
				@fclose($output);

				// Free Memory
				$this->MYSQL->free_result($rows);
			}

			// Unlock Tables
			if ($this->LOCK)
			{
				$this->MYSQL->query($unlock);
			}
		}
	}
	
	/* ------------- Primary Initiation Methods ------------- */

	// Cron Based Automated Backup
	function cronBackup()
	{
		/*
		  Set Full Dump Path.
		*/
		$this->FULL_PATH  = $this->DUMP_PATH.$this->DATE.'/'.$this->PREFIX.$this->DATE;

		/* Short Path */
		$this->SHORT_PATH = $this->DUMP_PATH.$this->DATE;

		/*
		  Close Forum.
		*/
		if ($this->SHUTDOWN) { $this->forumStatus(0); }

		/*
		  Remove previous SQL Files.
		*/
		$this->removeDir($this->SHORT_PATH);
		$this->createDir($this->SHORT_PATH);

		/* Clear Cache */
		clearstatcache();

		/*
		  Repair & Optimize.
		*/
		if ($this->REPAIR)
		{
			$this->repairTables();
		}

		/*
		  Start Initial Dump.
		*/
		$this->phpDump();
 
		/*
		  Reopen Forum.
		*/
		if ($this->SHUTDOWN) { $this->forumStatus(1); }
	}


	/* -------------- Named Constructor ------------- */

	// Initiate Constructor
	function mysqlBackup(&$config, &$dbclass, &$vboptions)
	{
		/* Load Error Codes */
		parent::loadCodes();

		/* Set Default Status */
		$this->STATUS = $this->MESSAGES[NO_ERRORS];

		/* Reference Database Object */
		$this->MYSQL = &$dbclass;

		/* Forum Shutdown System */
		$this->SHUTDOWN = &$config['SHUTDOWN'];
		$this->MESSAGE  = &$config['MESSAGE'];

		/* File Saving Information */
		$this->DATE      = date($config['DATE']);
		$this->PREFIX    = &$config['PREFIX'];
		$this->DUMP_PATH = &$config['DUMP_PATH'];

		/* Backup Method & Lock & Repair*/
		$this->LOCK   = &$config['LOCK'];
		$this->REPAIR = &$config['REPAIR'];

		/* Backup Type & Tables & Combine */
		$this->TABLES = array();
		$this->TYPE    = &$config['TYPE'];
		$this->TABS  = &$config['TABLES'];
		$this->COMBINE = &$config['COMBINE'];

		/* Backup Optimizations */
		$this->INNODB 	  = &$config['INNODB'];

		/* PHP Execution Function */
		$this->COMMAND = &$config['COMMAND'];

		/* Get Database Options */
		if ($this->SHUTDOWN)
		{
			/* Get Options */
			$this->OPTIONS = &$vboptions;

			/* Check if loaded OK */
			if (!is_array($this->OPTIONS) || empty($this->OPTIONS))
			{
				$this->throwError(DB_OPTIONS);
			}
		}

		/* Get Tables List */
		$list = $this->MYSQL->query('SHOW tables');
		while ($table = $this->MYSQL->fetch_array($list,DBARRAY_NUM))
		{
			if (in_array($table[0],$this->TABS))
			{
				if ($this->TYPE == 1) $this->TABLES[] = $table[0];
			}
			else
			{
				if ($this->TYPE == 2) $this->TABLES[] = $table[0];
			}
		}
	}
}

?>
