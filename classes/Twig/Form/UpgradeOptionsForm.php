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

namespace PrestaShop\Module\AutoUpgrade\Twig\Form;

use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;

class UpgradeOptionsForm
{
    /**
     * @var array
     */
    private $fields;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var FormRenderer
     */
    private $formRenderer;

    public function __construct(Translator $translator, FormRenderer $formRenderer)
    {
        $this->translator = $translator;
        $this->formRenderer = $formRenderer;

        $this->fields = [
            'PS_AUTOUP_PERFORMANCE' => [
                'title' => $translator->trans(
                    'Server performance',
                    [],
                    'Modules.Autoupgrade.Admin'
                ),
                'cast' => 'intval',
                'validation' => 'isInt',
                'defaultValue' => '1',
                'type' => 'select', 'desc' => $translator->trans(
                        'Unless you are using a dedicated server, select "Low".',
                        [],
                        'Modules.Autoupgrade.Admin'
                    ) . '<br />' .
                    $translator->trans(
                        'A high value can cause the upgrade to fail if your server is not powerful enough to process the upgrade tasks in a short amount of time.',
                        [],
                        'Modules.Autoupgrade.Admin'
                    ),
                'choices' => [
                    1 => $translator->trans(
                        'Low (recommended)',
                        [],
                        'Modules.Autoupgrade.Admin'
                    ),
                    2 => $translator->trans('Medium', [], 'Modules.Autoupgrade.Admin'),
                    3 => $translator->trans(
                        'High',
                        [],
                        'Modules.Autoupgrade.Admin'
                    ),
                ],
            ],
            'PS_AUTOUP_CUSTOM_MOD_DESACT' => [
                'title' => $translator->trans(
                    'Disable non-native modules',
                    [],
                    'Modules.Autoupgrade.Admin'
                ),
                'cast' => 'intval',
                'validation' => 'isBool',
                'type' => 'bool',
                'desc' => $translator->trans(
                        'As non-native modules can experience some compatibility issues, we recommend to disable them by default.',
                        [],
                        'Modules.Autoupgrade.Admin'
                    ) . '<br />' .
                    $translator->trans(
                        'Keeping them enabled might prevent you from loading the "Modules" page properly after the upgrade.',
                        [],
                        'Modules.Autoupgrade.Admin'
                    ),
            ],
            'PS_DISABLE_OVERRIDES' => [
                'title' => $translator->trans(
                    'Disable all overrides',
                    [],
                    'Modules.Autoupgrade.Admin'
                ),
                'cast' => 'intval',
                'validation' => 'isBool',
                'type' => 'bool',
                'desc' => $translator->trans(
                    'Enable or disable all classes and controllers overrides.',
                    [],
                    'Modules.Autoupgrade.Admin'
                ),
            ],
            'PS_AUTOUP_UPDATE_DEFAULT_THEME' => [
                'title' => $translator->trans(
                    'Upgrade the default theme',
                    [],
                    'Modules.Autoupgrade.Admin'
                ),
                'cast' => 'intval',
                'validation' => 'isBool',
                'defaultValue' => '1',
                'type' => 'bool',
                'desc' => $translator->trans(
                        'If you customized the default PrestaShop theme in its folder (folder name "classic" in 1.7), enabling this option will lose your modifications.',
                        [],
                        'Modules.Autoupgrade.Admin'
                    ) . '<br />'
                    . $translator->trans(
                        'If you are using your own theme, enabling this option will simply update the default theme files, and your own theme will be safe.',
                        [],
                        'Modules.Autoupgrade.Admin'
                    ),
            ],

            'PS_AUTOUP_CHANGE_DEFAULT_THEME' => [
                'title' => $translator->trans(
                    'Switch to the default theme',
                    [],
                    'Modules.Autoupgrade.Admin'
                ),
                'cast' => 'intval',
                'validation' => 'isBool',
                'defaultValue' => '0',
                'type' => 'bool',
                'desc' => $translator->trans(
                    'This will change your theme: your shop will then use the default theme of the version of PrestaShop you are upgrading to.',
                    [],
                    'Modules.Autoupgrade.Admin'
                ),
            ],

            'PS_AUTOUP_UPDATE_RTL_FILES' => [
                'title' => $translator->trans(
                    'Regenerate RTL stylesheet',
                    [],
                    'Modules.Autoupgrade.Admin'
                ),
                'cast' => 'intval',
                'validation' => 'isBool',
                'defaultValue' => '1',
                'type' => 'bool',
                'desc' => $translator->trans(
                    'If enabled, any RTL-specific files that you might have added to all your themes might be deleted by the created stylesheet.',
                    [],
                    'Modules.Autoupgrade.Admin'
                ),
            ],

            'PS_AUTOUP_KEEP_MAILS' => [
                'title' => $translator->trans(
                    'Keep the customized email templates',
                    [],
                    'Modules.Autoupgrade.Admin'
                ),
                'cast' => 'intval',
                'validation' => 'isBool',
                'type' => 'bool',
                'desc' => $translator->trans(
                        'This will not upgrade the default PrestaShop e-mails.',
                        [],
                        'Modules.Autoupgrade.Admin'
                    ) . '<br />'
                    . $translator->trans(
                        'If you customized the default PrestaShop e-mail templates, enabling this option will keep your modifications.',
                        [],
                        'Modules.Autoupgrade.Admin'
                    ),
            ],
        ];
    }

    public function render()
    {
        return $this->formRenderer->render(
            'upgradeOptions',
            $this->fields,
            $this->translator->trans(
                'Upgrade Options',
                [],
                'Modules.Autoupgrade.Admin'
            ),
            '',
            'prefs'
        );
    }
}
