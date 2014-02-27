<?php

class Task_Sys_Packages extends Task {

	public function getDescription() {
		return 'Install and upgrade packages';
	}

	protected function _getDependencies() {
		if (Role::OS_OSX == $this->getRole()->getOperatingsystem()) {
			return array(new Task_Sys_ScriptsInstall('brew.sh'), new Task_Sys_BrewUpgrade(), new Task_Sys_BrewInstall(), new Task_Sys_BrewCaskInstall());
		}
		return array();
	}

	protected function _run() {

	}
}
