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

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools;

use PrestaShop\Module\AutoUpgrade\Log\LoggerInterface;
use PrestaShop\Module\AutoUpgrade\Tools14;

class Translation
{
    /** @var string[] */
    private $installedLanguagesIso;
    /** @var LoggerInterface */
    private $logger;
    /** @var Translator */
    private $translator;

    /**
     * @param string[] $installedLanguagesIso
     */
    public function __construct(Translator $translator, LoggerInterface $logger, array $installedLanguagesIso)
    {
        $this->logger = $logger;
        $this->translator = $translator;
        $this->installedLanguagesIso = $installedLanguagesIso;
    }

    /**
     * @return string|false type of translation item
     */
    public function getTranslationFileType(string $file)
    {
        $type = false;
        // line shorter
        $separator = addslashes(DIRECTORY_SEPARATOR);
        $translation_dir = $separator . 'translations' . $separator;

        $regex_module = '#' . $separator . 'modules' . $separator . '.*' . $translation_dir . '(' . implode('|', $this->installedLanguagesIso) . ')\.php#';

        if (preg_match($regex_module, $file)) {
            $type = 'module';
        } elseif (preg_match('#' . $translation_dir . '(' . implode('|', $this->installedLanguagesIso) . ')' . $separator . 'admin\.php#', $file)) {
            $type = 'back office';
        } elseif (preg_match('#' . $translation_dir . '(' . implode('|', $this->installedLanguagesIso) . ')' . $separator . 'errors\.php#', $file)) {
            $type = 'error message';
        } elseif (preg_match('#' . $translation_dir . '(' . implode('|', $this->installedLanguagesIso) . ')' . $separator . 'fields\.php#', $file)) {
            $type = 'field';
        } elseif (preg_match('#' . $translation_dir . '(' . implode('|', $this->installedLanguagesIso) . ')' . $separator . 'pdf\.php#', $file)) {
            $type = 'pdf';
        } elseif (preg_match('#' . $separator . 'themes' . $separator . '(default|prestashop)' . $separator . 'lang' . $separator . '(' . implode('|', $this->installedLanguagesIso) . ')\.php#', $file)) {
            $type = 'front office';
        }

        return $type;
    }

    public function isTranslationFile(string $file): bool
    {
        return $this->getTranslationFileType($file) !== false;
    }

    /**
     * merge the translations of $orig into $dest, according to the $type of translation file.
     *
     * @param string $orig file from upgrade package
     * @param string $dest filepath of destination
     * @param string $type type of translation file (module, back office, front office, field, pdf, error)
     *
     * @return bool
     */
    public function mergeTranslationFile(string $orig, string $dest, string $type): bool
    {
        switch ($type) {
            case 'front office':
                $var_name = '_LANG';
                break;
            case 'back office':
                $var_name = '_LANGADM';
                break;
            case 'error message':
                $var_name = '_ERRORS';
                break;
            case 'field':
                $var_name = '_FIELDS';
                break;
            case 'module':
                $var_name = '_MODULE';
                break;
            case 'pdf':
                $var_name = '_LANGPDF';
                break;
            case 'mail':
                $var_name = '_LANGMAIL';
                break;
            default:
                return false;
        }

        if (!file_exists($orig)) {
            $this->logger->notice($this->translator->trans('[NOTICE] File %s does not exist, merge skipped.', [$orig]));

            return true;
        }
        include $orig;
        if (!isset($$var_name)) {
            $this->logger->warning($this->translator->trans(
                '[WARNING] %variablename% variable missing in file %filename%. Merge skipped.',
                [
                    '%variablename%' => $var_name,
                    '%filename%' => $orig,
                ]
            ));

            return true;
        }
        $var_orig = $$var_name;

        if (!file_exists($dest)) {
            $this->logger->notice($this->translator->trans('[NOTICE] File %s does not exist, merge skipped.', [$dest]));

            return false;
        }
        include $dest;
        if (!isset($$var_name)) {
            // in that particular case : file exists, but variable missing, we need to delete that file
            // (if not, this invalid file will be copied in /translations during upgradeDb process)
            if ('module' == $type) {
                unlink($dest);
            }
            $this->logger->warning($this->translator->trans(
                '[WARNING] %variablename% variable missing in file %filename%. File %filename% deleted and merge skipped.',
                [
                    '%variablename%' => $var_name,
                    '%filename%' => $dest,
                ]
            ));

            return false;
        }
        $var_dest = $$var_name;

        $merge = array_merge($var_orig, $var_dest);

        $fd = fopen($dest, 'w');
        if ($fd === false) {
            return false;
        }
        fwrite($fd, "<?php\n\nglobal \$" . $var_name . ";\n\$" . $var_name . " = array();\n");
        foreach ($merge as $k => $v) {
            if ('mail' == $type) {
                fwrite($fd, '$' . $var_name . '[\'' . $this->escape($k) . '\'] = \'' . $this->escape($v) . '\';' . "\n");
            } else {
                fwrite($fd, '$' . $var_name . '[\'' . $this->escape($k, true) . '\'] = \'' . $this->escape($v, true) . '\';' . "\n");
            }
        }
        fwrite($fd, "\n?>");
        fclose($fd);

        return true;
    }

    /**
     * Escapes illegal characters in a string.
     * Extracted from DB class, in order to avoid dependancies.
     *
     * @param string $str
     * @param bool $html_ok Does data contain HTML code ? (optional)
     *
     * @see DbCore::_escape()
     */
    private function escape(string $str, bool $html_ok = false): string
    {
        $search = ['\\', "\0", "\n", "\r", "\x1a", "'", '"'];
        $replace = ['\\\\', '\\0', '\\n', '\\r', "\Z", "\'", '\"'];
        $str = str_replace($search, $replace, $str);
        if (!$html_ok) {
            return strip_tags(Tools14::nl2br($str));
        }

        return $str;
    }
}
