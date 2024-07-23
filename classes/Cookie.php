<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace PrestaShop\Module\AutoUpgrade;

class Cookie
{
    const GENERATED_KEY_FILE = 'key.php';

    /**
     * @var string Admin subfolder, for cookie restricted use
     */
    private $adminDir;

    /**
     * @var string Path to the tmp folder for key storage
     */
    private $keyFilePath;

    /**
     * @var string Key kept in cache once loaded
     */
    private $key;

    /**
     * @param string $adminDir Admin subfolder
     * @param string $tmpDir Storage folder
     */
    public function __construct(string $adminDir, string $tmpDir)
    {
        $this->adminDir = $adminDir;
        $this->keyFilePath = $tmpDir . DIRECTORY_SEPARATOR . self::GENERATED_KEY_FILE;
    }

    /**
     * Create the cookie to be verified during the upgrade process,
     * because we can't use the classic authentication.
     *
     * @param string $iso_code i.e 'en'
     */
    public function create(int $idEmployee, string $iso_code): void
    {
        $this->storeKey(_COOKIE_KEY_);

        $cookiePath = __PS_BASE_URI__ . $this->adminDir;
        setcookie('id_employee', (string) $idEmployee, 0, $cookiePath);
        setcookie('iso_code', $iso_code, 0, $cookiePath);
        setcookie('autoupgrade', $this->encrypt((string) $idEmployee), 0, $cookiePath);
    }

    /**
     * From the cookie, check the current employee started the upgrade process.
     *
     * @param array<string, mixed> $cookie
     *
     * @return bool True if allowed
     */
    public function check(array $cookie): bool
    {
        if (empty($cookie['id_employee']) || empty($cookie['autoupgrade'])) {
            return false;
        }

        return $cookie['autoupgrade'] == $this->encrypt($cookie['id_employee']);
    }

    /**
     * @return string MD5 hashed string
     */
    private function encrypt(string $string): string
    {
        return md5(md5($this->readKey()) . md5($string));
    }

    /**
     * Generate PHP string to be stored in file.
     *
     * @return string PHP file content
     *
     * @internal
     */
    public function generateKeyFileContent(string $key): string
    {
        return '<?php
$key = "' . $key . '";
';
    }

    /**
     * If not loaded, reads the generated file to get the key.
     *
     * @internal
     */
    public function readKey(): string
    {
        if (!empty($this->key)) {
            return $this->key;
        }

        // Variable $key is defined in file
        $key = '';
        require $this->keyFilePath;
        $this->key = $key;

        return $this->key;
    }

    /**
     * PrestaShop constants won't be available during the upgrade process
     * We store it in a dedicated file.
     *
     * @return bool True on success
     *
     * @internal
     */
    public function storeKey(string $key): bool
    {
        return (bool) file_put_contents($this->keyFilePath, $this->generateKeyFileContent($key));
    }
}
