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
use Twig_Environment;

/**
 * Builds the upgrade checklist block.
 */
class UpgradeChecklist
{
    /**
     * @var Twig_Environment|\Twig\Environment
     */
    private $twig;

    /**
     * @var string
     */
    private $prodRootPath;

    /**
     * @var string
     */
    private $adminPath;

    /**
     * @var string
     */
    private $autoupgradePath;

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
     * @param Twig_Environment|\Twig\Environment $twig
     * @param UpgradeSelfCheck $upgradeSelfCheck
     * @param string $prodRootPath
     * @param string $adminPath
     * @param string $autoupgradePath
     * @param string $currentIndex
     * @param string $token
     */
    public function __construct(
        $twig,
        UpgradeSelfCheck $upgradeSelfCheck,
        $prodRootPath,
        $adminPath,
        $autoupgradePath,
        $currentIndex,
        $token
    ) {
        $this->twig = $twig;
        $this->selfCheck = $upgradeSelfCheck;
        $this->prodRootPath = $prodRootPath;
        $this->adminPath = $adminPath;
        $this->autoupgradePath = $autoupgradePath;
        $this->currentIndex = $currentIndex;
        $this->token = $token;
    }

    /**
     * Returns the block's HTML.
     *
     * @return string
     */
    public function render()
    {
        $data = [
            'showErrorMessage' => !$this->selfCheck->isOkForUpgrade(),
            'moduleVersion' => $this->selfCheck->getModuleVersion(),
            'moduleIsUpToDate' => $this->selfCheck->isModuleVersionLatest(),
            'moduleUpdateLink' => Context::getContext()->link->getAdminLink('AdminModulesUpdates'),
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
            'currentIndex' => $this->currentIndex,
            'token' => $this->token,
            'cachingIsDisabled' => $this->selfCheck->isCacheDisabled(),
            'maxExecutionTime' => $this->selfCheck->getMaxExecutionTime(),
            'phpUpgradeRequired' => $this->selfCheck->isPhpUpgradeRequired(),
            'checkPhpVersionCompatibility' => $this->selfCheck->isPhpVersionCompatible(),
            'checkApacheModRewrite' => $this->selfCheck->isApacheModRewriteEnabled(),
            'notLoadedPhpExtensions' => $this->selfCheck->getNotLoadedPhpExtensions(),
            'checkMemoryLimit' => $this->selfCheck->isMemoryLimitValid(),
            'checkFileUploads' => $this->selfCheck->isPhpFileUploadsConfigurationEnabled(),
            'notExistsPhpFunctions' => $this->selfCheck->getNotExistsPhpFunctions(),
            'checkPhpSessions' => $this->selfCheck->isPhpSessionsValid(),
            'missingFiles' => $this->selfCheck->getMissingFiles(),
            'notWritingDirectories' => $this->selfCheck->getNotWritingDirectories(),
        ];

        return $this->twig->render('@ModuleAutoUpgrade/block/checklist.twig', $data);
    }
}
