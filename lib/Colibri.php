<?php

if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

class Colibri
{
	public function __get($name)
	{
		// Reference system classes as items of the root System class
		// (it blends well with CI syntax :-)
		if (class_exists(__CLASS__.'\\'.$name)) {
			return $this->load($name);
		} else {
			trigger_error("$name is undefined");
			return;
		}
	}

	/**
	 * Autoload of classes from files
	 */
	static public function _autoload ($class)
	{
		if (substr_compare($class, __CLASS__, 0, strlen(__CLASS__)) === 0)
		{
			$path = dirname(__FILE__).DS.str_replace('\\', DS, $class).'.php';
			include_once ($path);
		}
	}

	/**
	 * Load a library class as singleton
	 */
	static private $_instances = array();
	static public function load ($class='Colibri')
	{
		switch ($class)
		{
			case __CLASS__:
				if (empty(self::$_instances[__CLASS__]))
					self::$_instances[__CLASS__] = new Colibri;
				return self::$_instances[__CLASS__];
			default:
				$class = __CLASS__."\\{$class}";
				if (empty(self::$_instances[$class]))
					self::$_instances[$class] = new $class;
				return self::$_instances[$class];
		}
	}

   /**
    * Shut down teh system
    *
    * @param int &$status Variable for storing command exit status
    * @param bool $return Only return command output instead of echoig it
    *
    * @return misc Command output
    */
   function shutdown (&$status, $return=FALSE)
   {
      $output = array();

      // Shutdown python script
      $mw_shutdown = 'sudo python /var/www/fabui/python/gmacro.py shutdown';
      if ($return)
         exec($mw_shutdown, $output, $status);
      else
         $output[] = system($mw_shutdown, $status);

      if ($status != 0)
         return $output;

      $cmds = array('/sbin/shutdown -h now', '/sbin/poweroff', '/sbin/halt');
      for ($status = -1; $status != 0 && count($cmds); )
      {
         $os_shutdown = 'sudo -E '.array_shift($cmds);
         if ($return)
            exec($os_shutdown, $output, $status);
         else
            $output[] = system($os_shutdown, $status);
      }

      return $output;
   }

}

spl_autoload_register('\Colibri::_autoload');
