<?php

class Task_Sys_BrewInstall extends Task {

    /** @var string[]|null */
    private $_packageList;

    public function getDescription() {
        return 'Brew packages install';
    }

    /**
     * @param string[]|null $packageList
     */
    public function __construct(array $packageList = null) {
        $this->_packageList = $packageList;
    }

    protected function _run() {
        if (null !== $this->_packageList) {
            $packages = $this->_packageList;
        } else {
            $packages = array_merge($this->_getPackages('_default'), $this->_getPackages('%ROLE%'));
            $packages = array_unique($packages);
        }

        $packagesInstalled = preg_split('/\n/', $this->exec('brew list'));

        foreach ($packages as $package) {
            if (!in_array($package, $packagesInstalled)) {
                $this->exec('brew install ' . $package);
            }
        }
    }

    /**
     * @param string $role
     * @return string[]
     */
    private function _getPackages($role) {
        $path = $this->getRole()->getDataPath('install/' . $role . '/brew.list');
        $packages = array();
        if (file_exists($path)) {
            $packages = file($path, FILE_IGNORE_NEW_LINES);
        }
        return array_filter($packages, 'trim');
    }
}
