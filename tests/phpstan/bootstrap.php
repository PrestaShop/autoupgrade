<?php

$rootDir = getenv('_PS_ROOT_DIR_');

if (!$rootDir) {
    echo '[ERROR] Define _PS_ROOT_DIR_ with the path to PrestaShop folder' . PHP_EOL;
    exit(1);
}

// Add module composer autoloader
require_once dirname(__DIR__) . '/../vendor/autoload.php';
// Add PrestaShop composer autoload
define('_PS_ADMIN_DIR_', $rootDir . '/admin-dev/');
define('PS_ADMIN_DIR', _PS_ADMIN_DIR_);
require_once $rootDir . '/config/defines.inc.php';
require_once $rootDir . '/config/autoload.php';
require_once $rootDir . '/config/bootstrap.php';

// Lib existing on PS 1.6
if (file_exists(_PS_TOOL_DIR_ . 'tar/Archive_Tar.php')) {
    require_once _PS_TOOL_DIR_ . 'tar/Archive_Tar.php';
}

// Make sure loader php-parser is coming from php stan composer
$loader = new \Composer\Autoload\ClassLoader();
$loader->setPsr4('PhpParser\\', array('/composer/vendor/nikic/php-parser/lib/PhpParser'));
$loader->register(true);

// We must declare these constant in this boostrap script.
// Ignoring the error partern with this value will throw another error if not found
// during the checks.
$constantsToDefine = [
  '_DB_SERVER_',
  '_DB_NAME_',
  '_DB_USER_',
  '_DB_PASSWD_',
  '_MYSQL_ENGINE_',
  '_COOKIE_KEY_',
  '_COOKIE_IV_',
  '_PS_VERSION_',
  '_DB_PREFIX_',
  '_PS_SSL_PORT_',
  '_THEME_NAME_',
  '_PARENT_THEME_NAME_',
  '__PS_BASE_URI__',
  '_PS_PRICE_DISPLAY_PRECISION_',
  '_PS_PRICE_COMPUTE_PRECISION_',
  '_PS_OS_CHEQUE_',
  '_PS_OS_PAYMENT_',
  '_PS_OS_PREPARATION_',
  '_PS_OS_SHIPPING_',
  '_PS_OS_DELIVERED_',
  '_PS_OS_CANCELED_',
  '_PS_OS_REFUND_',
  '_PS_OS_ERROR_',
  '_PS_OS_OUTOFSTOCK_',
  '_PS_OS_OUTOFSTOCK_PAID_',
  '_PS_OS_OUTOFSTOCK_UNPAID_',
  '_PS_OS_BANKWIRE_',
  '_PS_OS_PAYPAL_',
  '_PS_OS_WS_PAYMENT_',
  '_PS_OS_COD_VALIDATION_',
];
foreach ($constantsToDefine as $constant) {
    if (!defined($constant)) {
        define($constant, 'DUMMY_VALUE');
    }
}
