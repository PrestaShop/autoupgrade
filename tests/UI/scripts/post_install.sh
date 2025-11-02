#!/bin/bash
php -r "
require_once '/var/www/html/config/config.inc.php';
require_once '/var/www/html/init.php';
\$module = Module::getInstanceByName('autoupgrade');
if (\$module && \$module->install()) {
    echo '✅ Module autoupgrade installé et activé !\n';
} else {
    echo '❌ Erreur ou module autoupgrade introuvable\n';
}
"