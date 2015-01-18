<?php

class Host {

    /** @var string */
    private $_host;

    /** @var string */
    private $_name;

    /** @var Role */
    private $_role;

    /** @var string[] */
    private $_vars = array();

    /** @var null|resource */
    private $_sshMaster;

    /**
     * @param string        $host
     * @param string        $name
     * @param Role          $role
     * @param string[]|null $vars
     * @throws Exception
     */
    function __construct($host, $name, $role, $vars = null) {
        if (null === $vars) {
            $vars = array();
        }
        $this->_host = $host;
        $this->_name = $name;
        $this->_role = $role;
        $this->_vars = $vars;
    }

    function __destruct() {
        $this->_closeSshMaster();
    }

    /**
     * @return string
     */
    public function getHost() {
        return $this->_host;
    }

    public function getName() {
        return $this->_name;
    }

    public function getRole() {
        return $this->_role;
    }

    public function getVars() {
        return $this->_vars;
    }

    public function isLocal() {
        return ($this->getHost() == 'localhost' || $this->getHost() == '127.0.0.1');
    }

    /**
     * @param string $cmd
     * @param bool   $showOutput OPTIONAL
     * @return null|string
     */
    public function exec($cmd, $showOutput = false) {
        $cmd = $this->_replaceVariables($cmd);
        $this->getRole()->getDeployment()->echoFillLine(' ' . $cmd, "\r");
        if ($this->isLocal()) {
            $stdout = $this->_tryExec($cmd, $showOutput);
        } else {
            $stdout = $this->_tryExec('ssh ' . $this->_getSshOptions() . ' root@' . $this->getHost() . ' ' . escapeshellarg($cmd), $showOutput);
        }
        return $stdout;
    }

    /**
     * @param string $sourcePath
     * @param string $destPath
     */
    public function upload($sourcePath, $destPath) {
        $this->getRole()->getDeployment()->echoFillLine(' Upload: ' . $sourcePath . ' -> ' . $destPath, "\r");
        $sourcePath = $this->getRole()->getDataPath($sourcePath);
        $this->_upload($sourcePath, $destPath);
    }

    /**
     * @param string $sourcePath
     * @param string $destPath
     */
    public function uploadConfig($sourcePath, $destPath) {
        $this->getRole()->getDeployment()->echoFillLine(' Upload config: ' . $sourcePath . ' -> ' . $destPath, "\r");
        $sourcePath = $this->getRole()->getDeployment()->getConfigsPath() . $sourcePath;
        $this->_upload($sourcePath, $destPath);
    }

    /**
     * @param string $sourcePath
     * @param string $destPath
     */
    private function _upload($sourcePath, $destPath) {
        $sourcePath = $this->_replaceVariables($sourcePath);
        $destPath = $this->_replaceVariables($destPath);

        if (is_file($sourcePath)) {
            $content = file_get_contents($sourcePath);
            $contentReplace = $this->_replaceVariables($content);
            if ($content != $contentReplace) {
                $sourcePath = $this->getRole()->getDeployment()->createTmpFile($contentReplace);
            }
        }

        if ($this->isLocal()) {
            $this->_tryExec('sudo cp -R ' . escapeshellarg($sourcePath) . ' ' . escapeshellarg($destPath));
        } else {
            $this->_tryExec('scp -r ' . $this->_getSshOptions() . ' ' . escapeshellarg($sourcePath) . ' ' .
                escapeshellarg('root@' . $this->getHost() . ':' . escapeshellcmd($destPath)));
        }
    }

    /**
     * @param string      $cmd
     * @param bool|null   $echo OPTIONAL
     * @param string|null $stdin
     * @throws Exception
     * @return null|string
     */
    private function _tryExec($cmd, $echo = null, $stdin = null) {
        if ($echo) {
            $this->getRole()->getDeployment()->emptyLine();
        }
        $command = '/bin/bash -ec ' . escapeshellarg($cmd);
        $descriptorSpec = array(0 => array("pipe", "r"), 1 => array("pipe", "w"), 2 => array("pipe", "w"));
        $process = proc_open($command, $descriptorSpec, $pipes);
        if (!is_resource($process)) {
            throw new Exception('Cannot open command file pointer to `' . $command . '`');
        }

        if ($stdin) {
            fwrite($pipes[0], $stdin);
        }
        fclose($pipes[0]);

        $readStreams = array($pipes[1], $pipes[2]);
        $readStreamsOutput = array('', '');
        $readStreamsOutputCombined = '';
        $readStreamsOpen = $readStreams;
        while (true) {
            $readStreamsAvailable = $readStreamsOpen;
            $writeStreamsAvailable = array();
            $except = null;
            if (false === stream_select($readStreamsAvailable, $writeStreamsAvailable, $except, 0, 100000)) {
                throw new Exception('Failed to select process streams');
            }
            foreach ($readStreamsAvailable as $readStreamAvailable) {
                $i = array_search($readStreamAvailable, $readStreams, true);
                $output = fread($readStreamAvailable, 8192);
                if ($echo) {
                    echo $output;
                }
                $readStreamsOutput[$i] .= $output;
                $readStreamsOutputCombined .= $output;
                if (feof($readStreamAvailable)) {
                    fclose($readStreamAvailable);
                    $readStreamsOpen = array_splice($readStreamsOpen, $i, 0);
                }
            }
            if (empty($readStreamsOpen)) {
                break;
            }
        }

        $returnStatus = proc_close($process);
        if ($returnStatus != 0) {
            $this->getRole()->getDeployment()->echoLine('ERROR! Command failed: `' . $command . '`.');
            if ('' !== trim($readStreamsOutputCombined)) {
                $this->getRole()->getDeployment()->echoLine(trim($readStreamsOutputCombined));
            }

            $action = null;
            while ($action != 'c' && $action != 'a' && $action != 'r') {
                $action = $this->getRole()->getDeployment()->prompt('[C]ontinue, [A]bort, [R]etry?');
            }
            if ($action == 'r') {
                return $this->_tryExec($cmd, $echo, $stdin);
            } elseif ($action == 'a') {
                exit(1);
            }
        }
        return trim($readStreamsOutput[0]);
    }

    /**
     * @return string
     */
    private function _getSshControlPath() {
        $controlPath = '~/.ssh/master-%r@%h:%p';
        if (!$this->_sshMaster) {
            $this->_sshMaster = proc_open('ssh -NnMS ' . $controlPath . ' root@' . $this->getHost() . ' >/dev/null', array(), $pipes);
        }
        return $controlPath;
    }

    private function _closeSshMaster() {
        if ($this->_sshMaster) {
            $status = proc_get_status($this->_sshMaster);
            if ($status['running']) {
                // proc_terminate() doesnt work - https://bugs.php.net/bug.php?id=39992
                exec("ps -o pid,ppid -ax 2>/dev/null | awk '{ if ( $2 == " . $status['pid'] . " ) { print $1 }}'", $pids);
                $pids[] = $status['pid'];
                foreach ($pids as $pid) {
                    posix_kill($pid, 15);
                }
                proc_close($this->_sshMaster);
            }
        }
    }

    /**
     * @return string
     */
    private function _getSshOptions() {
        $options = '-o ControlPath=' . $this->_getSshControlPath();
        if ($this->getRole()->getDeployment()->getSkipStrictHostKeyChecking()) {
            $options .= ' -o StrictHostKeyChecking=no';
        }
        return $options;
    }

    private function _replaceVariables($text) {
        if (preg_match_all('#%(([\w_-]{1,50}):)?([\w_-]{3,50})%#', $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $host = $this;
                if ($match[2]) {
                    $host = $this->getRole()->getDeployment()->findHost($match[2]);
                    if (!$host) {
                        $role = $this->getRole()->getDeployment()->findRole($match[2]);
                        if (!$role) {
                            throw new Exception('Neither host or role `' . $match[2] . '` not found (can\'t retrieve variable value).');
                        }
                        $host = $role->getFirstHost();
                    }
                }
                $value = $host->_getVariableValue($match[3]);
                $text = str_ireplace($match[0], $value, $text);
            }
        }
        return $text;
    }

    private function _getVariableValue($var) {
        $var = strtolower($var);
        $vars = array_merge($this->getRole()->getDeployment()->getVars(), $this->getRole()->getVars(), $this->getVars());

        if (!array_key_exists($var, $vars)) {
            switch ($var) {
                case 'environment':
                    $value = $this->getRole()->getDeployment()->getEnvironment();
                    break;
                case 'home':
                    $value = $this->exec('echo $HOME');
                    break;
                case 'user':
                    $value = $this->exec('whoami');
                    break;
                case 'role':
                    $value = $this->getRole()->getName();
                    break;
                default:
                    $this->getRole()->getDeployment()->echoFillLine(
                        'VARIABLE: ' . strtoupper($var) . ' on ' . $this->getHost() . ' (' . $this->_getVariableValue('hostname') . ', role: ' .
                        $this->getRole()->getName() . ')');
                    $value = $this->getRole()->getDeployment()->prompt('Please specify value');
                    break;
            }
            $this->_vars[$var] = $value;
            return $this->_getVariableValue($var);
        }

        return $vars[$var];
    }
}
