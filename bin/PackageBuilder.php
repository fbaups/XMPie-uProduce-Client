<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('memory_limit', '2G');

use App\Utility\Releases\BuildTasks;

require __DIR__ . '/../config/paths.php';
require __DIR__ . '/../vendor/autoload.php';

$BuildTasks = new BuildTasks();
$BuildTasks->setAppName('SampleApp');
$BuildTasks->build();
