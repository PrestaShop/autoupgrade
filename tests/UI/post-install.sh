#!/bin/bash
set -e

# Boucle pour essayer d'installer le module jusqu'à 5 fois
for i in {1..7}; do
    echo "⏳ Tentative $i d'installation du module autoupgrade..."
    php -r '
    require_once "/var/www/html/config/config.inc.php";
    require_once "/var/www/html/init.php";

    if (!Module::isInstalled("autoupgrade")) {
        $module = Module::getInstanceByName("autoupgrade");
        if (!$module) {
            echo "⚠️ Module autoupgrade introuvable, retry...\n";
            exit(1);
        }
        if ($module->install()) {
            echo "✅ Module autoupgrade installé et activé !\n";
            exit(0);
        } else {
            echo "❌ Échec de installation du module.\n";
            exit(0);
        }
    } else {
        echo "ℹ️ Module déjà installé.\n";
        exit(1);
    }
    '
    sleep 2
done

exit 1
