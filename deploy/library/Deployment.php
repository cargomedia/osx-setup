<?php

class Deployment {

	/** @var Role[] */
	private $_roles = array();

	/** @var Task[] */
	private $_tasks = array();

	private $_scriptName = null;

	/** @var string[] */
	private $_vars = array();

	/** @var string[] */
	private $_args = array();

	/** @var string */
	private $_environment;

	/** @var bool */
	private $_skipStrictHostKeyChecking = false;

	/**
	 * @param string     $environment
	 * @param array|null $vars
	 */
	public function __construct($environment, array $vars = null) {
		if (null === $vars) {
			$vars = array();
		}
		$this->_environment = (string) $environment;
		$this->_vars = $vars;
	}

	/**
	 * @param string     $name
	 * @param string     $operatingsystem
	 * @param array|null $vars
	 * @return Role
	 */
	public function addRole($name, $operatingsystem, array $vars = null) {
		if (null === $vars) {
			$vars = array();
		}
		$name = strtolower($name);
		$role = new Role($name, $this, $operatingsystem, $vars);
		$this->_roles[$name] = $role;
		return $role;
	}

	/**
	 * @param Task[] $tasks
	 */
	public function addTasks($tasks) {
		if (!is_array($tasks)) {
			$tasks = func_get_args();
		}
		foreach ($tasks as $task) {
			$this->_tasks[strtolower($task->getName())] = $task;
		}
	}

	/**
	 * @param bool $state
	 */
	public function setSkipStrictHostKeyChecking($state) {
		$this->_skipStrictHostKeyChecking = (bool) $state;
	}

	/**
	 * @return bool
	 */
	public function getSkipStrictHostKeyChecking() {
		return $this->_skipStrictHostKeyChecking;
	}

	/**
	 * @param string $str
	 * @return Role[] array
	 */
	private function _selectRoles($str) {
		$str = strtolower($str);
		// Direct match
		if (isset($this->_roles[$str])) {
			return array($this->_roles[$str]);
		}
		// IP address
		if (preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $str, $matches)) {
			$ip = $matches[0];
			/** @var Role $role */
			foreach ($this->_roles as $rolename => $role) {
				/** @var Host $host */
				foreach ($role->getHosts() as $nodename => $host) {
					if ($nodename == $ip) {
						$selectedRole = new Role($rolename, $this, $role->getOperatingsystem(), $role->getVars());
						$selectedRole->addHost($ip, $host->getName(), $host->getVars());
						return array($selectedRole);
					}
				}
			}
		}
		// All (non-local)
		if ($str == 'all') {
			$selectedRoles = array();
			foreach ($this->_roles as $rolename => $role) {
				foreach ($role->getHosts() as $hostname => $host) {
					if (!$host->isLocal()) {
						$selectedRoles[] = $role;
						break;
					}
				}
			}
			return $selectedRoles;
		}
		// Nothing found
		return array();
	}

	/**
	 * @param $str
	 * @return Task|null
	 */
	private function _selectTask($str) {
		$str = strtolower($str);
		// Direct match
		if (isset($this->_tasks[$str])) {
			return $this->_tasks[$str];
		}
		// Abbrevated match
		$selectedTask = null;
		foreach ($this->_tasks as $taskName => $task) {
			if (strpos($taskName, $str) === 0) {
				if ($selectedTask) {
					$this->_usage('Task is ambiguous');
				}
				$selectedTask = $task;
			}
		}
		if ($selectedTask) {
			return $selectedTask;
		}
		// Nothing found
		return null;
	}

	/**
	 * @param string[] $argv
	 */
	public function run($argv) {
		try {
			$this->_scriptName = basename($argv[0]);
			if (count($argv) < 3) {
				$this->_usage();
			}
			$selectedRoles = $this->_selectRoles($argv[1]);
			$selectedTask = $this->_selectTask($argv[2]);

			if (empty($selectedRoles) || !$selectedTask) {
				$this->_usage();
			}
			if (count($argv) > 3) {
				$this->_args = array_slice($argv, 3);
			}

			foreach ($selectedRoles as $selectedRole) {
				$this->echoFillLine('ROLE: ' . $selectedRole->getName() . ' (' . count($selectedRole->getHosts()) . ' hosts)');
				$selectedTask->run($selectedRole);
			}

			$this->_clearTmpDir();
			$this->echoFillLine('Done.');
		} catch (Exception $e) {
			$this->echoLine('ERROR: ' . $e->getMessage());
			exit(1);
		}
	}

	/**
	 * @return Role[] array
	 */
	public function getRoles() {
		return $this->_roles;
	}

	/**
	 * @param string $name
	 * @return Host|null
	 */
	public function findHost($name) {
		foreach ($this->getRoles() as $role) {
			if ($host = $role->findHost($name)) {
				return $host;
			}
		}
		return null;
	}

	/**
	 * @param string $name
	 * @return Role|null
	 */
	public function findRole($name) {
		foreach ($this->getRoles() as $role) {
			if ($name === $role->getName()) {
				return $role;
			}
		}
		return null;
	}

	/**
	 * @return string[]
	 */
	public function getVars() {
		return $this->_vars;
	}

	/**
	 * @param int $index OPTIONAL
	 * @return bool
	 */
	public function hasArg($index = 0) {
		return array_key_exists($index, $this->_args);
	}

	/**
	 * @param int $index OPTIONAL
	 * @return array
	 */
	public function getArg($index = 0) {
		if (!$this->hasArg($index)) {
			echo 'ERROR: Missing argument #' . $index . PHP_EOL;
			exit(1);
		}
		return $this->_args[$index];
	}

	public function echoFillLine($msg, $eol = PHP_EOL) {
		$cols = `tput cols 2>/dev/null || echo 100`;
		if (strlen($msg) > $cols) {
			$msg = substr($msg, 0, $cols - 3) . '...';
		}
		echo $msg . str_repeat(' ', $cols - strlen($msg)) . $eol;
	}

	public function echoLine($msg, $eol = PHP_EOL) {
		echo $msg . $eol;
	}

	public function emptyLine($eol = "\r") {
		$this->echoFillLine('', $eol);
	}

	/**
	 * @param $question
	 * @return string
	 * @throws Exception
	 */
	public function prompt($question) {
		if (!posix_isatty(STDIN)) {
			throw new Exception('TTY requested but not available, prompting `' . $question . '`.');
		}
		echo $question . ': ';
		return rtrim(fgets(STDIN), "\n");
	}

	public function expandPwdPath($path) {
		if (substr($path, 0, 1) != '/') {
			$path = getcwd() . '/' . $path;
		}
		return $path;
	}

	/**
	 * @param string $dir
	 * @return string[]|array()
	 */
	public function findFiles($dir) {
		$files = array();
		if (!is_dir($dir)) {
			return $files;
		}
		if ($handle = opendir($dir)) {
			while (false !== ($file = readdir($handle))) {
				if (!in_array($file, array('.', '..', '.svn'))) {
					if (is_dir($dir . $file . '/')) {
						foreach ($this->findFiles($dir . $file . '/') as $subFile) {
							$files[] = $file . '/' . $subFile;
						}
					} else {
						$files[] = $file;
					}
				}
			}
			closedir($handle);
		}
		natsort($files);
		return $files;
	}

	/**
	 * @return string
	 */
	public function getTmpDir() {
		$dir = dirname(__DIR__) . '/tmp/';
		if (!file_exists($dir)) {
			mkdir($dir);
		}
		return $dir;
	}

	/**
	 * @return string
	 */
	public function getConfigsPath() {
		return dirname(__DIR__) . '/configs/' . $this->getEnvironment() . '/';
	}

	/**
	 * @return string
	 */
	public function getEnvironment() {
		return $this->_environment;
	}

	public function createTmpFile($content) {
		$filename = $this->getTmpDir() . uniqid('tmp', true);
		$handle = fopen($filename, 'w');
		fwrite($handle, $content);
		fclose($handle);
		return $filename;
	}

	/**
	 * @param string $hostname
	 * @return string|null
	 */
	public function nslookup($hostname) {
		$hostname = (string) $hostname;
		$commands = array(
			'grep ' . escapeshellarg($hostname) . ' /etc/hosts | awk \'{print $1}\'',
			'dig ' . escapeshellarg($hostname) . ' +short | tail -n 1',
		);
		foreach ($commands as $command) {
			exec($command, $result);
			if ($result) {
				return reset($result);
			}
		}
		return null;
	}

	private function _clearTmpDir() {
		foreach (glob($this->getTmpDir() . '*') as $tmpFile) {
			unlink($tmpFile);
		}
	}

	private function _usage($error = null) {
		if ($error) {
			echo 'ERROR: ' . $error . "\n\n";
		}

		echo "Usage: " . $this->_scriptName . " ROLE TASK [ARGS..]\n";

		echo "\n ROLE can be one of:\n";
		foreach ($this->_roles as $role) {
			echo " \t" . $role->getName() . "\n";
		}
		echo "\n TASK can be one of:\n";
		$len = 0;
		foreach ($this->_tasks as $task) {
			$len = max($len, strlen($task->getName()));
		}
		foreach ($this->_tasks as $task) {
			echo " \t" . $task->getName();
			if ($desc = $task->getDescription()) {
				echo str_repeat(' ', ($len - strlen($task->getName()))) . "  - $desc";
			}
			echo "\n";
		}

		exit(1);
	}
}

function __autoload($className) {
	$className = str_replace('_', '/', $className);
	require_once $className . '.php';
}
