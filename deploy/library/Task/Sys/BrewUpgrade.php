<?php

class Task_Sys_BrewUpgrade extends Task {
	public function getDescription() {
		return 'Brew packages upgrade';
	}

	protected function _run() {
		$this->exec('brew update');
		$this->exec('brew upgrade');
	}

}
