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

namespace PrestaShop\Module\AutoUpgrade\Twig\Block;

use Context;
use PrestaShop\Module\AutoUpgrade\Tools14;
use PrestaShop\Module\AutoUpgrade\UpgradeSelfCheck;
use Twig\Environment;

/**
 * Builds the upgrade checklist block.
 */
class UpgradeChecklist
{
    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var UpgradeSelfCheck
     */
    private $selfCheck;

    /**
     * @var string
     */
    private $currentIndex;

    /**
     * @var string
     */
    private $token;

    /**
     * UpgradeChecklistBlock constructor.
     *
     * @param Environment $twig
     * @param UpgradeSelfCheck $upgradeSelfCheck
     * @param string $currentIndex
     * @param string $token
     */
    public function __construct(
        $twig,
        UpgradeSelfCheck $upgradeSelfCheck,
        string $currentIndex,
        string $token
    ) {
        $this->twig = $twig;
        $this->selfCheck = $upgradeSelfCheck;
        $this->currentIndex = $currentIndex;
        $this->token = $token;
    }

    /**
     * @return array<string, mixed>
     */
    public function getTemplateVars(): array
    {
        return [
            'showErrorMessage' => !$this->selfCheck->isOkForUpgrade(),
            'moduleVersion' => $this->selfCheck->getModuleVersion(),
            'moduleIsUpToDate' => $this->selfCheck->isModuleVersionLatest(),
            'moduleUpdateLink' => Context::getContext()->link->getAdminLink('AdminModulesUpdates'),
            'isShopVersionMatchingVersionInDatabase' => $this->selfCheck->isShopVersionMatchingVersionInDatabase(),
            'adminToken' => Tools14::getAdminTokenLite('AdminModules'),
            'informationsLink' => Context::getContext()->link->getAdminLink('AdminInformation'),
            'maintenanceLink' => Context::getContext()->link->getAdminLink('AdminMaintenance'),
            'rootDirectoryIsWritable' => $this->selfCheck->isRootDirectoryWritable(),
            'rootDirectory' => _PS_ROOT_DIR_,
            'adminDirectoryIsWritable' => $this->selfCheck->isAdminAutoUpgradeDirectoryWritable(),
            'adminDirectoryWritableReport' => $this->selfCheck->getAdminAutoUpgradeDirectoryWritableReport(),
            'safeModeIsDisabled' => $this->selfCheck->isSafeModeDisabled(),
            'allowUrlFopenOrCurlIsEnabled' => $this->selfCheck->isFOpenOrCurlEnabled(),
            'zipIsEnabled' => $this->selfCheck->isZipEnabled(),
            'storeIsInMaintenance' => $this->selfCheck->isShopDeactivated(),
            'isLocalEnvironment' => $this->selfCheck->isLocalEnvironment(),
            'currentIndex' => $this->currentIndex,
            'token' => $this->token,
            'cachingIsDisabled' => $this->selfCheck->isCacheDisabled(),
            'maxExecutionTime' => $this->selfCheck->getMaxExecutionTime(),
            'phpRequirementsState' => $this->selfCheck->getPhpRequirementsState(),
            'phpCompatibilityRange' => $this->selfCheck->getPhpCompatibilityRange(),
            'checkApacheModRewrite' => $this->selfCheck->isApacheModRewriteEnabled(),
            'notLoadedPhpExtensions' => $this->selfCheck->getNotLoadedPhpExtensions(),
            'checkKeyGeneration' => $this->selfCheck->checkKeyGeneration(),
            'checkMemoryLimit' => $this->selfCheck->isMemoryLimitValid(),
            'checkFileUploads' => $this->selfCheck->isPhpFileUploadsConfigurationEnabled(),
            'notExistsPhpFunctions' => $this->selfCheck->getNotExistsPhpFunctions(),
            'checkPhpSessions' => $this->selfCheck->isPhpSessionsValid(),
            'missingFiles' => $this->selfCheck->getMissingFiles(),
            'notWritingDirectories' => $this->selfCheck->getNotWritingDirectories(),
        ];
    }

    /**
     * Returns the block's HTML.
     */
    public function render(): string
    {
        return $this->twig->render('@ModuleAutoUpgrade/block/checklist.html.twig', $this->getTemplateVars());
    }
}
