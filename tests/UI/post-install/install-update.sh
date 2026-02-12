#!/bin/bash
set -e

php -r '
require_once "/var/www/html/config/config.inc.php";
require_once "/var/www/html/init.php";

$module = Module::getInstanceByName("autoupgrade");

if ($module && !$module->installed) {
    $module->install();
}

if ($module && !$module->active) {
    $module->enable();
}

echo "Module installed and enabled\n";
'