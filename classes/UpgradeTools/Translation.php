<?php
/*
 * 2007-2018 PrestaShop
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 * 
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2018 PrestaShop SA
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools;

class Translation
{
    private $installedLanguagesIso;

    public function __construct($installedLanguagesIso)
    {
        $this->installedLanguagesIso = $installedLanguagesIso;
    }
    
    /**
     * getTranslationFileType
     *
     * @param string $file filepath to check
     * @access public
     * @return string type of translation item
     */
    public function getTranslationFileType($file)
    {
        $type = false;
        // line shorter
        $separator = addslashes(DIRECTORY_SEPARATOR);
        $translation_dir = $separator.'translations'.$separator;

        $regex_module = '#'.$separator.'modules'.$separator.'.*'.$translation_dir.'('.implode('|', $this->installedLanguagesIso).')\.php#';

        if (preg_match($regex_module, $file)) {
            $type = 'module';
        } elseif (preg_match('#'.$translation_dir.'('.implode('|', $this->installedLanguagesIso).')'.$separator.'admin\.php#', $file)) {
            $type = 'back office';
        } elseif (preg_match('#'.$translation_dir.'('.implode('|', $this->installedLanguagesIso).')'.$separator.'errors\.php#', $file)) {
            $type = 'error message';
        } elseif (preg_match('#'.$translation_dir.'('.implode('|', $this->installedLanguagesIso).')'.$separator.'fields\.php#', $file)) {
            $type = 'field';
        } elseif (preg_match('#'.$translation_dir.'('.implode('|', $this->installedLanguagesIso).')'.$separator.'pdf\.php#', $file)) {
            $type = 'pdf';
        } elseif (preg_match('#'.$separator.'themes'.$separator.'(default|prestashop)'.$separator.'lang'.$separator.'('.implode('|', $this->installedLanguagesIso).')\.php#', $file)) {
            $type = 'front office';
        }

        return $type;
    }

    public function isTranslationFile($file)
    {
        return ($this->getTranslationFileType($file) !== false);
    }
}