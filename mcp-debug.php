<?php
// Debug wrapper: log stderr to file, then run boost:mcp
chdir('C:\\claudeProjects\\TALLstack\\helpdesk');

$logFile = 'C:\\claudeProjects\\TALLstack\\helpdesk\\mcp-debug.log';
file_put_contents($logFile, date('H:i:s')." - started\n", FILE_APPEND);

// Redirect stderr to log
ini_set('error_log', $logFile);
ini_set('log_errors', 1);
ini_set('display_errors', 0);

$_SERVER['argv'] = ['artisan', 'boost:mcp'];
$_SERVER['argc'] = 2;

file_put_contents($logFile, date('H:i:s')." - loading artisan\n", FILE_APPEND);

try {
    require 'C:\\claudeProjects\\TALLstack\\helpdesk\\artisan';
} catch (\Throwable $e) {
    file_put_contents($logFile, date('H:i:s')." - ERROR: ".$e->getMessage()."\n".$e->getTraceAsString()."\n", FILE_APPEND);
}
