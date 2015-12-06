<?php

require_once 'library/Deployment.php';

$hostname = gethostname();
$dep = new Deployment('dev');
$dep->addRole('default', 'osx')->addHost('127.0.0.1', $hostname);

$taskSysInstall = new Task_Sys_Install();
$taskSysInstall->depends(
	new Task_Sys_ScriptsInstall('xcode.sh'),
	new Task_Sys_Packages(),
	new Task_Sys_Files(),
	new Task_Sys_ScriptsInstall()
);
$dep->addTasks($taskSysInstall);

$dep->run($argv);
