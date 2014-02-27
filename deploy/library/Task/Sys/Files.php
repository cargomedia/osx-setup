<?php

class Task_Sys_Files extends Task {

	/** @var string */
	private $_path;

	/** @var string[]|null */
	private $_excludedFiles = null;

	/**
	 * @param string|null   $path
	 * @param string[]|null $excludedFiles Array of regexps
	 */
	public function __construct($path = null, array $excludedFiles = null) {
		if (null === $path) {
			$path = '/';
		}
		$this->_path = (string) $path;
		if ($excludedFiles) {
			$this->_excludedFiles = $excludedFiles;
		}
	}

	public function getDescription() {
		return 'Upload files';
	}

	/**
	 * @param string   $string
	 * @param string[] $regex_array
	 * @return bool
	 */
	private function _in_array_match($string, $regex_array) {
		if (!is_array($regex_array)) {
			return false;
		}
		foreach ($regex_array as $expr) {
			$match = preg_match($expr, $string);
			if ($match === 1) {
				return true;
			}
		}
		return false;
	}

	protected function _run() {
		$madeDirs = array();
		$allFiles = $this->getRole()->findDataFiles('files/%ROLE%' . $this->_path);
		foreach ($allFiles as $file) {
			if ($this->_path === '/' && substr($file, 0, 5) === '_home') {
				$destPath = '%HOME%' . substr($file, 5);
				$mkdirUser = '%USER%';
			} else {
				$destPath = $this->_path . $file;
				$mkdirUser = 'root';
			}
			if (!in_array(dirname($destPath), $madeDirs)) {
				$this->exec('sudo -u ' . $mkdirUser . ' mkdir -p ' . dirname($destPath));
				$madeDirs[] = dirname($destPath);
			}
			if (!$this->_in_array_match($destPath, $this->_excludedFiles)) {
				$this->_preUploadProcessing($file, $destPath);
				$this->upload('files/%ROLE%' . $this->_path . $file, $destPath);
				$this->_postUploadProcessing($file, $destPath);
			}
		}
	}

	protected function _preUploadProcessing($file, $destPath) {
	}

	protected function _postUploadProcessing($file, $destPath) {
		if (preg_match('#^etc/init.d/([^/]+)$#', $file, $matches)) {
			$this->exec('chmod +x ' . $destPath);
			$this->exec('update-rc.d ' . $matches[1] . ' defaults');
		}

		if (preg_match('#sources.list.d/deb-multimedia\.list$#', $file)) {
			$this->exec('apt-get -q update');
			$this->exec('apt-get -qy --force-yes install deb-multimedia-keyring');
			$this->exec('apt-get -q update');
		}

		if (preg_match('#^etc/apt/[^/].*\.list#', $file)) {
			$this->exec('apt-get -q update');
		}

		if (preg_match('#^(/etc/fstab)\.d/#', $destPath, $matches)) {
			$cmd = 'if ! [ -f ' . $matches[1] . '.original ]; then cp ' . $matches[1] . ' ' . $matches[1] . '.original || touch ' .
					$matches[1] . '.original; fi;';
			$cmd .= 'cat ' . $matches[1] . '.original ' . $matches[1] . '.d/* >' . $matches[1] . ';';
			$this->exec($cmd);
		}

		if (preg_match('#^(.*/\.ssh/known_hosts)\.d/#', $destPath, $matches)) {
			$cmd = 'if ! [ -f ' . $matches[1] . '.original ]; then cp ' . $matches[1] . ' ' . $matches[1] . '.original || touch ' .
					$matches[1] . '.original; fi;';
			$cmd .= 'cat ' . $matches[1] . '.original ' . $matches[1] . '.d/* >' . $matches[1] . ';';
			$this->exec($cmd);
		}

		if (preg_match('#/\.ssh/([^/]+)$#', $file)) {
			$this->exec('sudo chmod 0600 ' . $destPath);
		}

		if (preg_match('#^(?:System/)?Library/LaunchAgents/([^/]+)\.plist$#', $file, $matches)) {
			$this->exec('sudo chown root ' . $destPath . ' && sudo chmod 744 ' . $destPath);
		}

		if (preg_match('#^(?:System/)?Library/LaunchDaemons/([^/]+)\.plist$#', $file, $matches)) {
			$this->exec('sudo chown root ' . $destPath . ' && sudo chmod 744 ' . $destPath);
			$this->exec('if ! (sudo launchctl list | grep -q ' . $matches[1] . '); then sudo launchctl load ' . $destPath . '; fi');
		}

		if (preg_match('#^%HOME%#', $destPath)) {
			$this->exec('sudo chown %USER% ' . $destPath);
		}
	}
}
