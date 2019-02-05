<?php

// Add module composer autoloader
require_once dirname(__DIR__) . "/../vendor/autoload.php";
// Add PrestaShop composer autoload
define('_PS_ADMIN_DIR_', '/web/PrestaShop/admin-dev/');
define('PS_ADMIN_DIR', _PS_ADMIN_DIR_);
require_once "/web/PrestaShop/config/defines.inc.php";
require_once "/web/PrestaShop/config/autoload.php";
require_once "/web/PrestaShop/config/bootstrap.php";

// Make sure loader php-parser is coming from php stan composer
$loader = new \Composer\Autoload\ClassLoader();
$loader->setPsr4('PhpParser\\', array('/composer/vendor/nikic/php-parser/lib/PhpParser'));
$loader->register(true);
