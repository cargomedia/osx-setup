<?php

class Task_Sys_ScriptsInstall extends Task {

    /** @var string|null */
    private $_scriptName;

    public function getDescription() {
        return 'Scripts install';
    }

    /**
     * @param string|null $scriptName
     */
    public function __construct($scriptName = null) {
        $this->_scriptName = $scriptName;
    }

    protected function _run() {
        if ($this->_scriptName) {
            $allFiles = array($this->_scriptName);
        } else {
            $allFiles = $this->getRole()->findDataFiles('install/%ROLE%/scripts/');
        }
        foreach ($allFiles as $file) {
            $this->execFile('install/%ROLE%/scripts/' . $file);
        }
    }
}
