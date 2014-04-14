<?php

class Task_Sys_BrewCaskInstall extends Task {

    public function getDescription() {
        return 'Brew casks install';
    }

    protected function _run() {
        $packages = array_merge($this->_getPackages('_default'), $this->_getPackages('%ROLE%'));
        $packages = array_unique($packages);

        $packagesInstalled = preg_split('/\n/', $this->exec('brew cask list'));

        foreach ($packages as $package) {
            if (!in_array($package, $packagesInstalled)) {
                $this->exec('brew cask install --appdir="/Applications" ' . $package);
            }
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
