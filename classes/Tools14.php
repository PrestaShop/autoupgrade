<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
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
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace PrestaShop\Module\AutoUpgrade;

use Tab;

/**
 * Useful collection of utilities that are guaranteed to work on every PHP and PrestaShop version supported.
 */
class Tools14
{
    protected static $_forceCompile;
    protected static $_caching;

    /**
     * Redirect user to another admin page.
     *
     * @param string $url Desired URL
     */
    public static function redirectAdmin(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    /**
     * getHttpHost return the <b>current</b> host used, with the protocol (http or https) if $http is true
     * This function should not be used to choose http or https domain name.
     *
     * @return string host
     */
    public static function getHttpHost(bool $http = false, bool $entities = false): string
    {
        $host = ($_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST']);
        if ($entities) {
            $host = htmlspecialchars($host, ENT_COMPAT, 'UTF-8');
        }
        if ($http) {
            $host = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') . $host;
        }

        return $host;
    }

    /**
     * Get a value from $_POST / $_GET
     * if unavailable, take a default value.
     *
     * @param string $key Value key
     * @param mixed $defaultValue (optional)
     *
     * @return mixed Value
     */
    public static function getValue(string $key, $defaultValue = false)
    {
        if (!isset($key) || empty($key) || !is_string($key)) {
            return false;
        }
        $ret = (isset($_POST[$key]) ? $_POST[$key] : (isset($_GET[$key]) ? $_GET[$key] : $defaultValue));

        if (is_string($ret) === true) {
            $ret = urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode($ret)));
        }

        return !is_string($ret) ? $ret : stripslashes($ret);
    }

    /**
     * Sanitize a string.
     *
     * @param string $string String to sanitize
     * @param bool $full String contains HTML or not (optional)
     *
     * @return string Sanitized string
     */
    public static function safeOutput(string $string, bool $html = false)
    {
        if (!$html) {
            $string = strip_tags($string);
        }

        return @self::htmlentitiesUTF8($string);
    }

    public static function htmlentitiesUTF8($string, int $type = ENT_QUOTES)
    {
        if (is_array($string)) {
            return array_map(['Tools', 'htmlentitiesUTF8'], $string);
        }

        return htmlentities($string, $type, 'utf-8');
    }

    /**
     * Delete directory and subdirectories.
     *
     * @param string $dirname Directory name
     */
    public static function deleteDirectory(string $dirname, bool $delete_self = true): bool
    {
        $dirname = rtrim($dirname, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (file_exists($dirname)) {
            if ($files = scandir($dirname)) {
                foreach ($files as $file) {
                    if ($file != '.' && $file != '..' && $file != '.svn') {
                        if (is_file($dirname . $file)) {
                            unlink($dirname . $file);
                        } elseif (is_dir($dirname . $file . DIRECTORY_SEPARATOR)) {
                            self::deleteDirectory($dirname . $file . DIRECTORY_SEPARATOR, true);
                        }
                    }
                }
                if ($delete_self && file_exists($dirname)) {
                    if (!rmdir($dirname)) {
                        return false;
                    }
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Check if submit has been posted.
     *
     * @param string $submit submit name
     */
    public static function isSubmit(string $submit): bool
    {
        return
            isset($_POST[$submit]) || isset($_POST[$submit . '_x']) || isset($_POST[$submit . '_y'])
            || isset($_GET[$submit]) || isset($_GET[$submit . '_x']) || isset($_GET[$submit . '_y'])
        ;
    }

    /**
     * Encrypt password.
     */
    public static function encrypt(string $passwd): string
    {
        return md5(pSQL(_COOKIE_KEY_ . $passwd));
    }

    /**
     * Encrypt password.
     *
     * @return false|string
     */
    public static function getAdminToken(string $string)
    {
        return !empty($string) ? self::encrypt($string) : false;
    }

    public static function getAdminTokenLite(string $tab)
    {
        global $cookie;

        return self::getAdminToken($tab . (int) Tab::getIdFromClassName($tab) . (int) $cookie->id_employee);
    }

    public static function strtolower($str)
    {
        if (is_array($str)) {
            return false;
        }
        if (function_exists('mb_strtolower')) {
            return mb_strtolower($str, 'utf-8');
        }

        return strtolower($str);
    }

    /**
     * Check config & source file to settle which dl method to use
     */
    public static function shouldUseFopen(string $url): bool
    {
        return in_array(ini_get('allow_url_fopen'), ['On', 'on', '1']) || !preg_match('/^https?:\/\//', $url);
    }

    public static function file_get_contents(string $url, bool $use_include_path = false, $stream_context = null, int $curl_timeout = 5)
    {
        if (!extension_loaded('openssl') && strpos('https://', $url) === true) {
            $url = str_replace('https', 'http', $url);
        }
        if ($stream_context == null && preg_match('/^https?:\/\//', $url)) {
            $stream_context = @stream_context_create(['http' => ['timeout' => $curl_timeout, 'header' => "User-Agent:MyAgent/1.0\r\n"]]);
        }
        if (self::shouldUseFopen($url)) {
            $var = @file_get_contents($url, $use_include_path, $stream_context);

            /* PSCSX-3205 buffer output ? */
            if (self::getValue('ajaxMode') && ob_get_level() && ob_get_length() > 0) {
                ob_clean();
            }

            if ($var) {
                return $var;
            }
        } elseif (function_exists('curl_init')) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($curl, CURLOPT_TIMEOUT, $curl_timeout);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            $opts = stream_context_get_options($stream_context);
            if (isset($opts['http']['method']) && self::strtolower($opts['http']['method']) == 'post') {
                curl_setopt($curl, CURLOPT_POST, true);
                if (isset($opts['http']['content'])) {
                    parse_str($opts['http']['content'], $datas);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $datas);
                }
            }
            $content = curl_exec($curl);
            curl_close($curl);

            return $content;
        }

        return false;
    }

    public static function nl2br(string $str): string
    {
        return str_replace(["\r\n", "\r", "\n"], '<br />', $str);
    }

    /**
     * Copy a file to another place
     *
     * @return bool True if the copy succeded
     */
    public static function copy(string $source, string $destination, $stream_context = null): bool
    {
        if (null === $stream_context && !preg_match('/^https?:\/\//', $source)) {
            return @copy($source, $destination);
        }

        $destFile = fopen($destination, 'wb');
        if (!is_resource($destFile)) {
            return false;
        }

        if (self::shouldUseFopen($source)) {
            $sourceFile = fopen($source, 'rb');
            // If something else than false, the data was stored
            $result = (file_put_contents($destination, $sourceFile) !== false);
            fclose($sourceFile);
        } elseif (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $source);
            curl_setopt($ch, CURLOPT_FILE, $destFile);
            $result = curl_exec($ch);
            curl_close($ch);
        }

        fclose($destFile);

        return $result;
    }
}
