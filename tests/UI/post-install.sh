#!/bin/bash
set +e

for i in {1..7}; do
    echo "⏳ Attempting $i to install the autoupgrade module"

    result=$(php -r '
        require_once "/var/www/html/config/config.inc.php";
        require_once "/var/www/html/init.php";

        if (!Module::isInstalled("autoupgrade")) {
            $module = Module::getInstanceByName("autoupgrade");
            if (!$module) {
                echo "⚠️ Autoupgrade module not found\n";
                exit(1);
            }
            if ($module->install()) {
                echo "✅ Autoupgrade module installed and activated !\n";
                exit(0);
            } else {
                echo "❌ Module installation failed.";
            }
        } else {
            echo "Module already installed..\n";
            exit(0);
        }
    ')

    if [[ $result == *"installed"* ]]; then
      break
    fi

    sleep 2
done

set -e
