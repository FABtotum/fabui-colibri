<?php

if (file_exists('log4php/Logger.php'))
{
	require_once 'log4php/Logger.php';
	Logger::configure('/var/www/fabui/config/log_database_config.xml');
}

class Database
{
	private $_driver;
	private $_backend;

	protected $_hostname;
	protected $_username;
	protected $_password;
	protected $_database;
	protected $_db;
	protected $_result;
	protected $_num_rows;
	protected $_log;

	private function _logError()
	{
		if (class_exists('Logger')) {
			return call_user_func_array(array($this->_log, 'error'), func_get_args());
		} else {
			return error_log(implode(' ', func_get_args()));
		}
	}

   /**
    *
    *
    */
	public function __construct()
	{
        $this->_init();
	}

   /**
    *
    *
    */
	public function _init ()
	{
		$this->_hostname = DB_HOSTNAME;
		$this->_username = DB_USERNAME;
		$this->_password = DB_PASSWORD;
		$this->_database = DB_DATABASE;
		$this->_num_rows = 0;

		if (class_exists('Logger')) {
			$this->_log = Logger::getLogger(__CLASS__);
		}

		if (!defined('DB_DRIVER'))
			define('DB_DRIVER', 'mysqli');

		// Determine driver from first segment of DB_DRIVER parameter
		$this->_driver = array_shift(explode(':', DB_DRIVER));
		switch ($this->_driver)
		{
			case 'mysqli':
				$this->_db = new mysqli($this->_hostname, $this->_username, $this->_password, $this->_database);
				if (mysqli_connect_errno()) {
					$this->_logError("DBMS connection error: ".mysqli_connect_error());
				}
				return TRUE;
			case 'pdo':
				// Set pdo dsn according to backend (second segment of driver parameter)
				$this->_backend = array_pop(explode(':', DB_DRIVER));
				if ($this->_backend == $this->_driver)
					throw new Error("Undefined database backend for PDO driver");
				switch($this->_backend)
				{
					case 'mysql':
						$dsn = "mysql:host={$this->_hostname};dbname={$this->_database}";
						break;
					case 'sqlite':
						if (!file_exists($this->_database)) {
							$this->_logError("SQLite database missing: {$this->_database}");
							return FALSE;
						} else {
							$dsn = "sqlite:{$this->_database}";
							break;
						}
					default:
						$dsn = $this->_backend;
				}
				try {
					$this->_db = new PDO($dsn, empty($this->_username)? NULL : $this->_username, empty($this->_password)? NULL : $this->_password);
					$this->_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
					return TRUE;
				}
				catch (PDOException $ex) {
					$this->_logError("DB connection error: ".$ex->getMessage());
					return FALSE;
				}
			default:
				throw new Exception("Undefined database driver: {$driver}");
				return FALSE;
		}
   }

   /**
    * Executes an arbitary query, possibily with parameters values, and returns
	* the rows retrieved from a 'SELECT' statement if any, null if none, or false
	* if any error occurred.
    */
	public function query ($query, $values=NULL)
	{
		// Prepare and interpolate parameters, if given
		if (isset($values)) {
			$st = $this->_db->prepare($query);
			$ret = $st->execute($values);
			$this->_result = $ret===FALSE? $ret : $st->fetchAll();
		} else {
			$this->_result = $this->_db->query($query);
		}

		if ($this->_result === FALSE)
		{
			$this->_logError("Query failed: ".$query);
			$this->_logError("Error message: ".$this->_db->error);  //TODO: make this driver independent
			return false;
		}

		if (is_object($this->_result))
		{
			
			$rc = get_class($this->_result);
			
			
			
			switch ($rc)
			{
				case 'PDOStatement':
					$this->_num_rows = $this->_result->rowCount();
					$this->_rows = $this->_result->fetchAll(PDO::FETCH_ASSOC);
					//print_r($this->_rows);exit();
					if (is_array($this->_rows) and count($this->_rows) > $this->_num_rows)
						$this->_num_rows = count($this->_rows);
					break;
				case 'mysqli_result':
      			$this->_num_rows = $this->_result->num_rows;
					$this->_rows = $this->_result->fetch_all(MYSQLI_ASSOC);
					break;
				default:
					throw new Exception("Unmanaged result type return from query: {$rc}");
			}

			if ($this->_rows === FALSE) {
				return FALSE;
			}

			if (is_array($this->_rows) and count($this->_rows) == $this->_num_rows)
			{
				//return $this->_num_rows==1? $this->_rows[0] : $this->_rows;
				return $this->_rows;
			}
			else
			{
				return $this->_num_rows;  // false?
			}
       }
    }


	public function close()
	{
		switch ($this->_driver)
		{
			case 'mysqli':
				$this->_db->close();
				return;
			case 'pdo':
			default:
				$this->_db = null;
				return;
		}
	}

	public function insert ($table_name, $data)
	{
      $_columns = '(`'.implode('`,`', array_keys($data)).'`)';

		switch ($this->_driver)
		{
			case 'pdo':
				// Use prepared statement to inject values
				$_values = '('.implode(',', array_map(function () { return '?'; }, array_keys($data))).')';
				break;
			case 'mysqli':
        		$_values = '(';
        		foreach ($data as $key => $value) {
					$_val = mysqli_real_escape_string($this->_db, $value);
					$_values .= $this->quote_value($_val).',';
        		}
        		$_values .= ')';
        		$_values = str_replace(',)', ')', $_values);
				break;
			default:
				throw new Exception ("Undefined DB driver in insert");
		}

      $_query = "INSERT INTO `{$table_name}`{$_columns} VALUES {$_values};";

      $r = $this->query($_query, $this->_driver=='pdo'? array_values($data) : NULL);

		if ($r !== FALSE) switch ($this->_driver)
		{
			case 'pdo': return $this->_db->lastInsertId();
			case 'mysqli': return $this->_db->insert_id;
			default: throw new Exception ("Undefined DB driver in insert");
		}
		else
		{
			return $r;
		}
    }

	public function update($table_name, $condition, $data)
	{
		$_updates = implode(', ', array_map(function ($key) { return "`{$key}`=?";}, array_keys($data)));
		$_values = array_values($data);
		$_query = "UPDATE {$table_name} SET {$_updates} where {$condition['column']} {$condition['sign']} ?";
		$_values[] = $condition['value'];

		return $this->query($_query, $_values);
	}


	public function get_num_rows(){
		return $this->_num_rows;
	}



    private function quote_value($value){


        if(is_numeric($value)){
            return $value;
        }


        if(strtolower($value) == 'now()'){
            return $value;
        }


        if(strcmp(trim(strtolower($value)), 'now()') == 0){
            return $value;
        }

        return '\''.$value.'\'';


    }

}

?>
