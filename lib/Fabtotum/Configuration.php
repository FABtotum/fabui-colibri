<?php namespace Fabtotum;

/** WIP **/

class Configuration
{
	private static $instance = array();
	public static function instance ($name='default')
	{
		if (empty($this->instance[$name]))
			$this->instance[$name] = new Configuration();
		return $this->instance[$name];
	}

	/**
	 * Read application configuration (in the form od PHP constants)
	 * recursively from $path upward $levels levels or until /, including
	 * any `config.php` file found on the way.
	 */
	public function loadAppConfiguration ($path, $levels=-1)
	{
	}

	private $preferences;

	/**
	 * Loads user customization for specific $app.conf inside standard
	 * directories: /etc/fabtotum/$app.conf, ~/.fabtotum/$app.conf
	 */
	public function loadUserPreferences ($app)
	{
		$user = 'root';
		$defaultPath = "/etc/fabtotum/{$app}.conf";
		$userPath = "{$user}/.fabtotum/{$app}.conf";
		$configuration = array();

		if (file_exists($defaultPath)) {
			$conf = file_get_contents($defaultPath);
			$configuration = array_merge_recursive($configuration, json_decode($conf, FALSE));
		}

		if (file_exists($userPath)) {
			$conf = file_get_contents($userPath);
			$configuration = array_merge_recursive($configuration, json_decode($conf, FALSE));
		}

		return $configuration;
	}

	public function saveUserPreferences ($app, $prefs)
	{
		$user = 'root';
		$userPath = "{$user}/.fabtotum/{$app}.conf";
		$conf = json_encode($prefs);
		file_put_contents($userPath, $conf, LOCK_EX);
	}

}
