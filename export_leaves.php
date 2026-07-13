<?php

// Autoload classes
require_once 'vendor/autoload.php';

use app\services\LeaveExportService;

$service = new LeaveExportService();
$service->run();
