<?php

class Task_Sys_BrewCaskInstall extends Task {

	public function getDescription() {
		return 'Brew casks install';
	}

	protected function _run() {
		$packages = array_merge($this->_getPackages('_default'), $this->_getPackages('%ROLE%'));
		$packages = array_unique($packages);

		foreach ($packages as $package) {
			$this->exec('if (brew cask list ' . $package . ' 2>&1 | grep "is not installed"); then brew cask install --appdir="/Applications" ' . $package . '; fi');
		}
	}

	/**
	 * @param string $role
	 * @return string[]
	 */
	private function _getPackages($role) {
		$path = $this->getRole()->getDataPath('install/' . $role . '/brew-cask.list');
		$packages = array();
		if (file_exists($path)) {
			$packages = file($path, FILE_IGNORE_NEW_LINES);
		}
		return array_filter($packages, 'trim');
	}
}
