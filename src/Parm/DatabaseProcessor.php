<?php

namespace Parm;

class DatabaseProcessor
{
	var $databaseNode;
	
	protected $sql = null;
	
	/**
	 * @param Database|DatabaseNode|string $mixed The database to connect to
     */
	function __construct($mixed)
	{
		// setup node
		if($mixed instanceof DatabaseNode)
		{
			$this->databaseNode = $mixed;
		}
		else if($mixed instanceof Database)
		{
			$this->databaseNode = $mixed->getMaster();
		}
		else if(is_string($mixed))
		{
			$this->databaseNode = ParmConfig::getDatabaseMaster($mixed);

			if($this->databaseNode == null || !($this->databaseNode instanceof DatabaseNode))
			{
				throw new \Parm\Exception\ErrorException("Unable to find database named " . htmlentities($mixed) . " in \\Parm\\ParmConfig configuration.");
			}
		}
		else
		{
			throw new \Parm\Exception\ErrorException("A Database, DatabaseNode, or \\Parm\\ParmConfig must be used for Parm to work.");
		}
		
	}
	
	/**
	 * Get the rows as an associative array
	 * 
	 * @return array
     */
	public function getArray()
	{
		$data = array();

		$this->process(function($obj) use (&$data)
		{
			$data[] = (array)$obj;
		});

		return $data;
	}
	
	
	/**
	 * Get the rows as an associative array JSON-ified with camelCase array keys 
	 * @return array
     */
	public function getJSON()
	{
		$data = array();

		$this->process(function($obj) use (&$data)
		{
			$data[] = $obj->toJSON();
		});

		return $data;
	}

	
	/**
	 * Get a single dimension array of values
	 * 
	 * @return array
     */
	public function getSingleColumnArray($columnName = null)
	{
		$data = array();
		
		$conn = $this->databaseNode->getConnection();
		
		$result = $this->getMySQLResult($this->getSQL(),$conn);
		
		if($result != null)
		{
			if($this->getNumberOfRowsFromResult($result) > 0)
			{
				$result->data_seek(0);
				
				while($row = $result->fetch_array($columnName ? MYSQLI_ASSOC : MYSQLI_NUM))
				{
					$data[] = $row[$columnName ? $columnName : 0];
				}
			}
		}
		
		return $data;
	}
	
	
	/**
	 * Get the first value from a single column from the database
	 * 
	 * @param string $columnName The name of the column to select from
	 * @return array
     */
	public function getFirstField($columnName)
	{
		$a = $this->getArray();
		
		if(is_array($a))
		{
			$a = reset($a);
			return $a[$columnName];
		}
	}
	
	/**
	 * Set the SQL to proccess
	 * 
	 * @param string $sql
	 * @return DatabaseProcessor
     */
	public function setSQL($sql)
	{
		$this->sql = $sql;
		return $this;
	}
	
	/**
	 * Get the SQL that has been set
	 * @return string
     */
	public function getSQL()
	{
		return $this->sql;
	}
	
	/**
	 * Build a data object from the row data
	 * 
	 * @param array $row The associative array of data
	 * @return DataArray
     */
	protected function loadDataObject(Array $row)
	{
		return new DataArray($row);
	}
	
	/**
	 * Loop through the rows of a query and process with a closure
	 * 
	 * @param callable $closure Closure to process the rows of the database retrieved with, the closure is passed a DataArray or DataAccessObject
	 * @return DatabaseProcessor This DatabaseProcessor so you can chain it
     */
	public function process($closure)
	{
		$conn = $this->databaseNode->getConnection();
		
		$result = $this->getMySQLResult($this->getSQL(),$conn);
		
		if($result != null)
		{
			if($this->getNumberOfRowsFromResult($result) > 0)
			{
				$result->data_seek(0);
				
				while ($row = $result->fetch_assoc())
				{
					$closure($this->loadDataObject($row));
				}
			}
		}
	
		$this->freeResult($result);
		
		return $this;
	}
	
	/**
	 * Using an Unbuffered Query, Loop through the rows of a query and process with a closure
	 * You can use this on millions of rows without memory problems
	 * Does lock the table to writes on some databases
	 * 
	 * @param callable $closure Closure to process the rows of the database retrieved with, the closure is passed a DataArray or DataAccessObject
	 * @return DatabaseProcessor This DatabaseProcessor so you can chain it
     */
	public function unbufferedProcess($closure)
	{
		$conn = $this->databaseNode->getConnection();
		
		$conn->real_query($this->getSQL());
		
		$result = $conn->use_result();
		
		while ($row = $result->fetch_assoc())
		{
			$closure($this->loadDataObject($row));
		}
		
		$this->freeResult($result);
		
		return $this;
		
	}
	
	/**
	 * Get the number of rows for a query from the MySQL database via the result
	 * 
	 * @param mysqli $result
	 * @return integer The number of rows reported from the database
     */
	public function getNumberOfRowsFromResult($result)
	{
		return (int)$result->num_rows;
	}
	
	/**
	 * Execute the query stored by setSQL()
	 * 
	 * @return mysql result
     */
	private function query()
	{
		if(count(func_get_args()) > 0)
		{
			throw new \Parm\Exception\ErrorException("DatabaseProcessor query does not accept any parameters");
		}
		
		$result = $this->getMySQLResult($this->getSQL());
		return $result;
	}
	
	/**
	 * Execute a sql update
	 * 
	 * @param string $sql The SQL to execute
     */
	public function update($sql)
	{
		$result = $this->getMySQLResult($sql);
		$this->freeResult($result);
	}
	
	public function executeMultiQuery()
	{
		return $this->__multiQuery();
	}
	
	
	/**
	 * Get a MySQL result from a SQL string
	 * 
	 * @param string $sql The SQL to execute
	 * @return mysql result
     */
	public function getMySQLResult($sql)
	{
		$conn = $this->databaseNode->getConnection();
		
		try 
		{
			$result = $conn->query($sql);
			if($conn->error != null)
			{
				throw new \Parm\Exception\ErrorException($conn->error);
			}
			else
			{
				return $result;
			}
		}
		catch(\Parm\Exception\ErrorException $e)
		{
			throw new \Parm\Exception\ErrorException("DatabaseProcessor SQL Error. MySQL Query Failed: " . htmlentities($sql) . '. Reason given ' . $e);
		}
	}
	
	/**
	 * Get the id of the last inserted object from the database node
	 * 
	 * @param string $sql The SQL to execute
	 * @return mysql result
     */
	public function getLastInsertId()
	{
		return $this->databaseNode->getLastInsertId();
	}
	
	/**
	 * Convert a datetime from one timezone to another using the MySQL database as the timezone source. Use the "US/Eastern" format or "Europe/London" formats
	 * 
	 * @param timestamp|string|DateTime $dateTime The datetime in the source timezone
	 * @param string $sourceTimezone The source timezone. "US/Eastern" mysql format (mysql.time_zone_name)
	 * @param string $destTimezone The destination timezone. "US/Eastern" mysql format (mysql.time_zone_name)
	 * @return \DateTime
     */
	function convertTimezone($dateTime,$sourceTimezone,$destTimezone)
	{
		if($dateTime === NULL)
		{
			return NULL;
		}
		else if($dateTime instanceof \DateTime)
		{
			$dateTimeObject = $dateTime;
		}
		else if(is_numeric($dateTime))
		{
			$dateTimeObject = new \DateTime();
			$dateTimeObject->setTimestamp($dateTime);
		}
		else
		{
			$dateTimeObject = new \DateTime($dateTime);
		}
		
		$this->setSQL("SELECT CONVERT_TZ('" . $dateTimeObject->format($this->databaseNode->getDatetimeStorageFormat()) . "','" . $this->escapeString($sourceTimezone) . "','" . $this->escapeString($destTimezone) . "') as convertTimezone;");
		
		$result = $this->getSingleColumnArray("convertTimezone");
		if(is_array($result))
		{
			$val = $result[0];
			if($val != "")
			{
				return new \DateTime(reset($result));
			}
			else
			{
				throw new TimezoneConversionException("Timezone conversion failed. Possible invalid timezone.");
			}
		}
		else
		{
			throw new TimezoneConversionException("Timezone conversion failed");
		}
	}
	
	/**
	 * Free a mysqli_result
     */
	public function freeResult($result)
	{
		if($result != null)
		{
			try
			{
				if($result instanceof mysqli_result)
				{
					$result->free();
				}
				
			}
			catch(ErrorException $e)
			{
				// Do nothing. (My eyes! The goggles do nothing!)
			}
		}
	}
	
	/**
	 * Output a JSON string using a real_query from the SQL that has been set using setSQL($sql)
     */
	public function outputJSONString()
	{
		echo "[";
		
			$firstRecord = true;
			
			$conn = $this->databaseNode->getConnection();
			
			$conn->real_query($this->getSQL());

			$result = $conn->use_result();

			while ($row = $result->fetch_assoc())
			{
				if(!$firstRecord)
				{
					echo ",";
				}
				else
				{
					$firstRecord = false;
				}
				
				$obj = $this->loadDataObject($row);
				
				echo $obj->toJSONString();
			}

			$this->freeResult($result);
		
		echo "]";

		return true;
	}
	
	/**
	 * Escape a string to prevent mysql injection
     */
	public function escapeString($string)
	{
		$conn = $this->databaseNode->getConnection();

		return $conn->real_escape_string($string);
	}
	
	/**
	 * Format some text for CSV
     */
	public static function formatTextCSV($text)
	{
		$text = preg_replace("/<(.|\n)*?>/","",$text);
	
		$text = str_replace("<br/>","\n",$text);
	
		$text = str_replace("&nbsp;"," ",$text);
	
		if(strpos($text,'"') === true)
		{
			$text = '"' . str_replace('"','""',$text) . '"';
		}
		else if(strpos($text,',') || strpos($text,"\n") || strpos($text,"\r"))
		{
			$text = '"' . str_replace('"','""',$text) . '"';
		}
	
		return html_entity_decode($text);
	}
	
	/**
	 * Useful for replacing mysql_real_escape_string in old code with DatabaseProcessor::mysql_real_escape_string()
     */
	public static function mysql_real_escape_string($string)
	{
		$firstAvailableDatabaseMaster = ParmConfig::__getFirstDatabaseMaster();

		if($firstAvailableDatabaseMaster == null || !($firstAvailableDatabaseMaster instanceof DatabaseNode))
		{
			throw new \Parm\Exception\ErrorException("DatabaseProcess::mysql_real_escape_string requires ParmConfig");
		}

		$dp = new DatabaseProcessor(ParmConfig::__getFirstDatabaseMaster($string));
		return $dp->escapeString($string);
	}
	
	private function __multiQuery()
	{
		$conn = $this->databaseNode->getConnection();
		
		$conn->multi_query($this->getSQL());
		
		do
		{
			if($conn->errno != 0)
			{
				throw new \Parm\Exception\ErrorException("Parm DatabaseProcessor multiQuery SQL Error. Reason given " . $conn->error);
			}
			
			if(!$conn->more_results() || (!$conn->next_result() && $conn->error == null))
			{
				break;
			}
			
		} while (true);
		
	}
	
}
