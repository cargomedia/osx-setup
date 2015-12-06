<?php

class Task_Sys_BrewCaskInstall extends Task {

    public function getDescription() {
        return 'Brew casks install';
    }

    protected function _getDependencies() {
        return array(
            new Task_Sys_BrewInstall(array('brew-cask')),
        );
    }

    protected function _run() {
        $packages = array_merge($this->_getPackages('_default'), $this->_getPackages('%ROLE%'));
        $packages = array_unique($packages);

        $packagesInstalled = preg_split('/\n/', $this->exec('OUT=$(brew cask list 2>&1) && echo "${OUT}" || test "${OUT}" = "Error: nothing to list" || (echo "${OUT}"; false)'));

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
        $packages = array_filter($packages, function ($package) {
            return !preg_match('/^#/', $package);
        });
        $packages = array_filter($packages, 'trim');
        return $packages;
    }
}
