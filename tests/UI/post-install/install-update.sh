#!/bin/bash
set -e

php -r '
require_once "/var/www/html/config/config.inc.php";
require_once "/var/www/html/init.php";

$module = Module::getInstanceByName("autoupgrade");
$module->install();
$module->enable();

echo "Module installed\n";
