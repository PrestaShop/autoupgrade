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

use PrestaShop\Module\AutoUpgrade\Log\LoggerInterface;

class Workspace
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var UpgradeTools\Translator
     */
    private $translator;

    /**
     * @var array List of paths used by autoupgrade
     */
    private $paths;

    public function __construct(LoggerInterface $logger, $translator, array $paths)
    {
        $this->logger = $logger;
        $this->translator = $translator;
        $this->paths = $paths;
    }

    public function createFolders()
    {
        foreach ($this->paths as $path) {
            if (!file_exists($path) && !mkdir($path)) {
                $this->logger->error($this->translator->trans('Unable to create directory %s', [$path], 'Modules.Autoupgrade.Admin'));
            }
            if (!is_writable($path)) {
                $this->logger->error($this->translator->trans('Unable to write in the directory "%s"', [$path], 'Modules.Autoupgrade.Admin'));
            }
        }
    }
}
