<?php

// used statically (lazy initialization)
class DBPal
{
	private static $dbh = null;
	
	static function quote($val)
	{
		// need to be connected before escaping (charset business)
		self::checkConnection();
		return "'" . self::$dbh->real_escape_string($val) . "'";
	}
	
	static function str2null($s)
	{
		return (($s !== null)? self::quote($s) : "NULL");
	}
	
	static function int2null($i)
	{
		return (($i !== null)? ((int) $i) : "NULL");
	}
	
	static function var2null($var)
	{
		return (is_int($var)? self::int2null($var) : self::str2null($var));
	}
	
	static function arr2set($arr)
	{
		$s = ""; $i = 0;
		
		foreach ($arr as $key => $val)
		{
			if ($i > 0)
				$s .= ", ";
			
			$s .= "$key = " . self::var2null($val);
			
			$i++;
		}
		
		return $s;
	}
	
	static function arr2values($arr)
	{
		// TODO: streamline programmation logic: lookup for vs foreach
		$s = "("; $i = 0;
		
		foreach ($arr as $key => $val)
		{
			if ($i > 0)
				$s .= ", ";
			
			$s .= $key;
			
			$i++;
		}
		
		$s .= ") VALUES (";
		
		$i = 0;
		foreach ($arr as $key => $val)
		{
			if ($i > 0)
				$s .= ", ";
			
			$s .= self::var2null($val);
			
			$i++;
		}
		
		$s .= ")";
		
		return $s;
	}
	
	static function query($queryString)
	{
		self::checkConnection();
		$result = self::$dbh->query($queryString);
		
		// TODO: remove for security issues, debug feature
		if (!$result)
			die(
			    "MySQL error: " . self::$dbh->error . "\n"
			  . "Query: $queryString\n"
			);
		
		return $result;
	}
	
	static function insert($queryString)
	{
		$result = self::query($queryString);
		
		return ($result)? self::$dbh->insert_id : null;
	}
	
	// can be replaced with mysqli::fetch_all with mysqlnd
	static function getAll($queryString)
	{
		$result = self::query($queryString);
		
		$arr = array();
		while ($row = $result->fetch_object())
		{
			$arr[] = $row;
		}
		
		return $arr;
	}
	
	static function getList($queryString)
	{
		$result = self::query($queryString);
		
		$arr = array();
		while ($row = $result->fetch_array(MYSQLI_NUM))
		{
			$arr[] = $row[0];
		}
		
		return $arr;
	}
	
	static function getRow($queryString)
	{
		$result = self::query($queryString);
		return $result->fetch_object();
	}
	
	static function getOne($queryString)
	{
		$result = self::query($queryString);
		$row = $result->fetch_row();
		return sizeof($row)? $row[0] : null;
	}
	
	static function finish()
	{
		// if the db connection is open, close it
		if (self::$dbh)
		{
			self::$dbh->close();
			self::$dbh = null;
		}
	}
	
	private static function checkConnection()
	{
		// if the db connection is not open yet, open it
		if (!self::$dbh) {
			self::$dbh = mysqli_init();
			$result = self::$dbh->real_connect(Settings::DB_HOST, Settings::DB_USER,
			                         Settings::DB_PASS, Settings::DB_BASE);
			// shouldn't libmysql automatically use default server setting for client charset?
			self::$dbh->set_charset("utf8");
			
			// set the session timezone (required for date calculation on queries)
			// real timezone identifier is require to avoid DST issues
			self::$dbh->query("SET time_zone = '" . date('e') . "';");
		}
	}
}
