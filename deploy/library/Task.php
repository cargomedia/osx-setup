<?php

abstract class Task {

    /** @var Role */
    private $_role;

    /** @var Task[] */
    private $_dependencies = array();

    public function getName() {
        $className = get_class($this);
        $name = str_replace(get_class() . '_', '', $className);
        return $name;
    }

    public function getDescription() {
        return null;
    }

    /**
     * @param Role $role
     * @param int  $runLevel OPTIONAL
     */
    public function run(Role $role, $runLevel = 0) {
        $this->_role = $role;
        $role->getDeployment()->echoFillLine(str_repeat(' ', $runLevel++) . '* Running ' . $this->getName() . '...');
        foreach ($this->_getDependencies() as $dependency) {
            $dependency->run($role, $runLevel);
        }
        foreach ($this->_dependencies as $dependency) {
            $dependency->run($role, $runLevel);
        }
        $this->_run();
    }

    /**
     * @param Task[] $tasks
     */
    public function depends($tasks) {
        if (!is_array($tasks)) {
            $tasks = func_get_args();
        }
        $this->_dependencies = array_merge($this->_dependencies, $tasks);
    }

    /**
     * @return Role
     */
    protected function getRole() {
        return $this->_role;
    }

    /**
     * @param string $cmd
     * @param bool   $echo
     * @return string|null
     */
    protected function exec($cmd, $echo = false) {
        $last_line = null;
        foreach ($this->getRole()->getHosts() as $host) {
            $last_line = $host->exec($cmd, $echo);
        }
        return $last_line;
    }

    /**
     * @param string $if bash test condition
     * @param string $cmd
     * @return null|string
     */
    protected function execIf($if, $cmd) {
        $cmd = "if [[ $if ]]; then $cmd; fi;";
        return $this->exec($cmd);
    }

    protected function execEcho($cmd) {
        $this->exec($cmd, true);
    }

    /**
     * @param string $file
     * @param bool   $echo OPTIONAL
     */
    protected function execFile($file, $echo = false) {
        $destDir = '/tmp/' . uniqid();
        $this->exec('mkdir -p ' . $destDir);
        $this->upload($file, $destDir . '/' . basename($file));
        $this->exec('cd ' . $destDir . ' && TERM=vt100 bash -e ' . basename($file) . ' && cd .. && rm -rf ' . $destDir, $echo);
    }

    protected function upload($source, $dest) {
        foreach ($this->getRole()->getHosts() as $host) {
            $host->upload($source, $dest);
        }
    }

    protected function uploadConfig($source, $dest) {
        foreach ($this->getRole()->getHosts() as $host) {
            $host->uploadConfig($source, $dest);
        }
    }

    protected function uploadConfigs($sourceDir, $destDir) {
        $sourcePath = $this->getRole()->getDeployment()->getConfigsPath() . $sourceDir;
        $configFiles = $this->getRole()->getDeployment()->findFiles($sourcePath);
        foreach ($configFiles as $configFile) {
            $this->uploadConfig($sourceDir . $configFile, $destDir . $configFile);
        }
    }

    /**
     * @return Task[]
     */
    protected function _getDependencies() {
        return array();
    }

    abstract protected function _run();
}
