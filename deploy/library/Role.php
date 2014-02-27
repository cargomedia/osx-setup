<?php

class Role {
	const OS_OSX = 'osx';

	/** @var string */
	private $_operatingsystem;

	/** @var string */
	private $_name;

	/** @var Deployment */
	private $_deployment;

	/** @var string[] */
	private $_vars = array();

	/** @var Host[] */
	private $_hosts = array();

	/**
	 * @param string              $name
	 * @param Deployment          $deployment
	 * @param string              $operatingsystem
	 * @param string[]            $vars
	 */
	function __construct($name, Deployment $deployment, $operatingsystem, array $vars = array()) {
		$this->_name = $name;
		$this->_deployment = $deployment;
		$this->_operatingsystem = (string) $operatingsystem;
		$this->_vars = $vars;
	}

	public function getName() {
		return $this->_name;
	}

	public function getVars() {
		return $this->_vars;
	}

	/**
	 * @return Deployment
	 */
	public function getDeployment() {
		return $this->_deployment;
	}

	/**
	 * @return string
	 */
	public function getOperatingsystem() {
		return $this->_operatingsystem;
	}

	/**
	 * @param string          $host
	 * @param string          $name
	 * @param string[]|null   $vars
	 * @return Role
	 */
	public function addHost($host, $name, $vars = null) {
		if (null === $vars) {
			$vars = array();
		}
		$this->_hosts[$host] = new Host($host, $name, $this, $vars);
		return $this;
	}

	/**
	 * @return Host[]
	 */
	public function getHosts() {
		return $this->_hosts;
	}

	/**
	 * @param Host $host
	 * @return bool
	 */
	public function hasHost($host) {
		return array_key_exists($host, $this->_hosts);
	}

	/**
	 * @param string $name
	 * @return Host|null
	 */
	public function findHost($name) {
		foreach ($this->getHosts() as $host) {
			if (strtolower($name) === strtolower($host->getName())) {
				return $host;
			}
		}
		return null;
	}

	/**
	 * @return Host
	 * @throws Exception
	 */
	public function getFirstHost() {
		if (!$this->_hosts) {
			throw new Exception('No hosts for `' . $this->getName() . '` role');
		}
		return reset($this->_hosts);
	}

	/**
	 * @return string
	 */
	public function getResourcePath() {
		return dirname(__DIR__) . '/resource/' . $this->getOperatingsystem() . '/';
	}

	public function expandResourcePath($path) {
		if (substr($path, 0, 1) != '/') {
			$path = $this->getResourcePath() . $path;
		}
		return $path;
	}

	/**
	 * @param string $path
	 * @return string
	 */
	public function getDataPath($path) {
		$path = $this->expandResourcePath($path);
		if (file_exists($this->_getPathRole($path))) {
			return $this->_getPathRole($path);
		}
		return $this->_getPathDefault($path);
	}

	/**
	 * @param string $dir
	 * @return string[]|array()
	 */
	public function findDataFiles($dir) {
		$dir = $this->expandResourcePath($dir);
		$filesDefault = $this->getDeployment()->findFiles($this->_getPathDefault($dir));
		$filesRole = $this->getDeployment()->findFiles($this->_getPathRole($dir));
		return array_unique(array_merge($filesDefault, $filesRole));
	}

	/**
	 * @param string $path
	 * @return string
	 */
	private function _getPathDefault($path) {
		return str_replace('%ROLE%', '_default', $path);
	}

	/**
	 * @param string $path
	 * @return string
	 */
	private function _getPathRole($path) {
		return str_replace('%ROLE%', strtolower($this->getName()), $path);
	}

}
