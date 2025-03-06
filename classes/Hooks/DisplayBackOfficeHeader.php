<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\AutoUpgrade\Hooks;

use Context;
use Employee;
use Exception;
use PrestaShop\Module\AutoUpgrade\DocumentationLinks;
use PrestaShop\Module\AutoUpgrade\Exceptions\DistributionApiException;
use PrestaShop\Module\AutoUpgrade\Exceptions\UpgradeException;
use PrestaShop\Module\AutoUpgrade\Models\UpdateNotificationConfiguration;
use PrestaShop\Module\AutoUpgrade\Services\UpdateNotificationService;
use PrestaShop\Module\AutoUpgrade\Twig\PageSelectors;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use PrestaShop\Module\AutoUpgrade\Upgrader;
use PrestaShop\Module\AutoUpgrade\VersionUtils;
use Symfony\Component\HttpFoundation\Request;
use Tab;
use Tools;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class DisplayBackOfficeHeader
{
    const INTERVAL_CHECK_TIME_IN_SECONDS = 2592000; // 30 days

    /**
     * @var UpgradeContainer
     */
    private $container;

    /**
     * @var Upgrader
     */
    private $upgrader;

    /**
     * @var UpdateNotificationService
     */
    private $updateNotificationService;

    /**
     * @var UpdateNotificationConfiguration
     */
    private $updateNotificationConfiguration;

    /**
     * @var string
     */
    private $content = '';

    /**
     * @var string
     */
    private $psVersion;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var int
     */
    private $employeeId;

    /**
     * @throws Exception
     */
    public function __construct(UpgradeContainer $container)
    {
        $this->container = $container;
        $this->upgrader = $this->container->getUpgrader();
        $this->updateNotificationService = $this->container->getUpdateNotificationService();
        $this->updateNotificationConfiguration = $this->updateNotificationService->getUpdateNotificationConfiguration();
        $this->psVersion = $this->container->getProperty(UpgradeContainer::PS_VERSION);
        $this->context = Context::getContext();
        $this->employeeId = $this->context->employee->id;
    }

    /**
     * @return string
     *
     * @throws DistributionApiException
     * @throws UpgradeException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function renderUpdateNotification(): string
    {
        if (
            !$this->updateNotificationConfiguration->getTimestamp()
            || (time() - $this->updateNotificationConfiguration->getTimestamp()) > self::INTERVAL_CHECK_TIME_IN_SECONDS
        ) {
            $this->checkNewerVersion();
        }

        $this->addScriptsVariables();

        $request = Request::createFromGlobals();
        $this->addUIAssets($request);

        if (!$this->isEmployeeDefaultController()) {
            return $this->content;
        }

        $employees = $this->updateNotificationConfiguration->getEmployees();

        $employeeExists = array_filter($employees, function ($employee) {
            return $employee['employeeID'] === $this->employeeId;
        });

        if ((empty($employeeExists) || time() > $employeeExists[0]['timestamp']) && $this->updateIsAvailable()) {
            $this->content .= $this->container->getTwig()->render('@ModuleAutoUpgrade/hooks/external-layout.html.twig', $this->getParams());
        }

        return $this->content;
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    private function addScriptsVariables(): void
    {
        $adminDir = trim(str_replace(_PS_ROOT_DIR_, '', realpath(_PS_ADMIN_DIR_)), DIRECTORY_SEPARATOR);

        $scriptsVariables = [
            'token' => Tools::getAdminTokenLite('AdminAutoupgradeAjax'),
            'admin_url' => __PS_BASE_URI__ . $adminDir,
            'admin_dir' => $adminDir,
        ];

        $this->content .= $this->container->getTwig()->render('@ModuleAutoUpgrade/module-script-variables.html.twig', [
            'autoupgrade_variables' => $scriptsVariables,
        ]);
    }

    /**
     * @param Request $request
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    private function addUIAssets(Request $request): void
    {
        $assetsEnvironment = $this->container->getAssetsEnvironment();
        $assetsBaseUrl = $assetsEnvironment->getAssetsBaseUrl($request);
        $twig = $this->container->getTwig();

        if ($assetsEnvironment->isDevMode()) {
            $this->context->controller->addCSS($assetsBaseUrl . '/src/scss/appUpdateNotification/main.scss');
            $this->content .= $twig->render('@ModuleAutoUpgrade/module-script-tag.html.twig', ['module_type' => true, 'src' => $assetsBaseUrl . '/src/ts/appUpdateNotification/main.ts']);
        } else {
            $this->context->controller->addCSS($assetsBaseUrl . '/css/autoupgrade-.css');
            $this->content .= $twig->render('@ModuleAutoUpgrade/module-script-tag.html.twig', ['src' => $assetsBaseUrl . '/js/autoupgrade.js?version=' . $this->psVersion]);
        }
    }

    private function isEmployeeDefaultController(): bool
    {
        $controller = Tools::getValue('controller');
        $employee = new Employee($this->employeeId);
        $default_tab_id = $employee->default_tab;

        $tab = new Tab($default_tab_id);
        $default_controller = $tab->class_name;

        return $controller === $default_controller;
    }

    /**
     * @throws DistributionApiException
     * @throws UpgradeException
     */
    private function checkNewerVersion(): void
    {
        $this->updateNotificationConfiguration->setTimestamp(time());

        if ($this->upgrader->isNewerVersionAvailableOnline()) {
            $onlineDestination = $this->upgrader->getOnlineDestinationRelease();

            $onlineVersion = $onlineDestination->getVersion();
            $this->updateNotificationConfiguration->setVersion($onlineVersion);

            $releaseNote = $onlineDestination->getReleaseNoteUrl();
            $this->updateNotificationConfiguration->setReleaseNote($releaseNote);
        }

        $this->updateNotificationService->saveUpdateNotificationConfiguration($this->updateNotificationConfiguration);
    }

    private function updateIsAvailable(): bool
    {
        return !empty($this->updateNotificationConfiguration->getVersion()) && version_compare($this->updateNotificationConfiguration->getVersion(), $this->psVersion, '>');
    }

    /**
     * @return array<string, mixed>
     *
     * @throws UpgradeException
     */
    private function getParams(): array
    {
        $psClass = '';

        if (version_compare($this->psVersion, '1.7.8.0', '<')) {
            $psClass = 'v1-7-3-0';
        } elseif (version_compare($this->psVersion, '9.0.0', '<')) {
            $psClass = 'v1-7-8-0';
        }

        $onlineVersion = $this->updateNotificationConfiguration->getVersion();

        $updateType = VersionUtils::getUpdateType($this->psVersion, $onlineVersion);

        return [
            'external_parent_id' => PageSelectors::NOTIFICATION_PARENT_ID,
            'component' => 'dialog-update-notification',
            'version_class' => $psClass,
            'version_type' => $updateType,
            'version' => $onlineVersion,
            'contact_expert_url' => DocumentationLinks::PRESTASHOP_EXPERTS,
            'release_note' => $this->updateNotificationConfiguration->getReleaseNote(),
        ];
    }
}
