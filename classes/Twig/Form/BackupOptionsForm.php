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

class BackupOptionsForm
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
            'PS_AUTOUP_BACKUP' => [
                'title' => $this->translator->trans(
                    'Back up my files and database',
                    [],
                    'Modules.Autoupgrade.Admin'
                ),
                'cast' => 'intval',
                'validation' => 'isBool',
                'defaultValue' => '1',
                'type' => 'bool',
                'desc' => $this->translator->trans(
                    'Automatically back up your database and files in order to restore your shop if needed. This is experimental: you should still perform your own manual backup for safety.',
                    [],
                    'Modules.Autoupgrade.Admin'
                ),
            ],
            'PS_AUTOUP_KEEP_IMAGES' => [
                'title' => $this->translator->trans(
                    'Back up my images',
                    [],
                    'Modules.Autoupgrade.Admin'
                ),
                'cast' => 'intval',
                'validation' => 'isBool',
                'defaultValue' => '1',
                'type' => 'bool',
                'desc' => $this->translator->trans(
                    'To save time, you can decide not to back your images up. In any case, always make sure you did back them up manually.',
                    [],
                    'Modules.Autoupgrade.Admin'
                ),
            ],
        ];
    }

    public function render()
    {
        return $this->formRenderer->render(
                'backupOptions',
                $this->fields,
                $this->translator->trans(
                    'Backup Options',
                    [],
                    'Modules.Autoupgrade.Admin'
                ),
                '',
                'database_gear'
            );
    }
}
