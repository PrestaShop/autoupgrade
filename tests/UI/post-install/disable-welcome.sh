#!/bin/bash

php -r "
require_once '/var/www/html/config/config.inc.php';
require_once '/var/www/html/init.php';
\$module = Module::getInstanceByName('welcome');
if (\$module && \$module->active) {
    if (\$module->disable()) {
        echo \"✅ Module ps_welcome successfully disabled.\n\";
    } else {
        echo \"❌ Error disabling the welcome module.\n\";
    }
} else {
    echo \"ℹ️ Module welcome already disabled or not found.\n\";
}
"
