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

class Translator
{
    private $caller;

    public function __construct($caller)
    {
        $this->caller = $caller;
    }

    /**
     * Translate a string to the current language.
     *
     * This methods has the same signature as the 1.7 trans method, but only relies
     *  on the module translation files.
     *
     * @param string $id Original text
     * @param array $parameters Parameters to apply
     * @param string $domain Unused
     * @param string $locale Unused
     *
     * @return string Translated string with parameters applied
     */
    public function trans($id, array $parameters = [], $domain = 'Modules.Autoupgrade.Admin', $locale = null)
    {
        // If PrestaShop core is not instancied properly, do not try to translate
        if (!method_exists('\Context', 'getContext') || null === \Context::getContext()->language) {
            return $this->applyParameters($id, $parameters);
        }

        if (method_exists('\Translate', 'getModuleTranslation')) {
            $translated = \Translate::getModuleTranslation('autoupgrade', $id, $this->caller, null);
            if (!count($parameters)) {
                return $translated;
            }
        } else {
            $translated = $id;
        }

        return $this->applyParameters($translated, $parameters);
    }

    /**
     * @param string $id
     * @param array $parameters
     *
     * @return string Translated string with parameters applied
     *
     * @internal Public for tests
     */
    public function applyParameters($id, array $parameters = [])
    {
        // Replace placeholders for non numeric keys
        foreach ($parameters as $placeholder => $value) {
            if (is_int($placeholder)) {
                continue;
            }
            $id = str_replace($placeholder, $value, $id);
            unset($parameters[$placeholder]);
        }

        return call_user_func_array('sprintf', array_merge([$id], $parameters));
    }
}
