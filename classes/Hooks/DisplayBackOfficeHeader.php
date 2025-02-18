<?php

namespace PrestaShop\Module\AutoUpgrade\Hooks;

use PrestaShop\Module\AutoUpgrade\Models\UpdateNotificationConfiguration;
use PrestaShop\Module\AutoUpgrade\Services\UpdateNotificationService;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use PrestaShop\Module\AutoUpgrade\Upgrader;
use PrestaShop\Module\AutoUpgrade\Router\Routes;
use PrestaShop\Module\AutoUpgrade\Twig\PageSelectors;
use PrestaShop\Module\AutoUpgrade\VersionUtils;

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
     * @var UpdateNotificationConfiguration
     */
    private $updateNotificationConfiguration;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->container = new UpgradeContainer(_PS_ROOT_DIR_, realpath(_PS_ADMIN_DIR_));
        $this->upgrader = $this->container->getUpgrader();
        $this->updateNotificationConfiguration = (new UpdateNotificationService())->getUpdateNotificationConfiguration();
    }

    /**
     * @return string
     *
     * @throws \PrestaShop\Module\AutoUpgrade\Exceptions\DistributionApiException
     * @throws \PrestaShop\Module\AutoUpgrade\Exceptions\UpgradeException
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function renderUpdateNotification(): string
    {
        if (
            !$this->updateNotificationConfiguration->getTimestamp()
            || (time() - $this->updateNotificationConfiguration->getTimestamp()) > self::INTERVAL_CHECK_TIME_IN_SECONDS
        ) {
            $this->checkNewerVersion();
        }

        $currentEmployeeId = \Context::getContext()->employee->id;

        $employees = $this->updateNotificationConfiguration->getEmployees();

        $employeeExists = array_filter($employees, function($employee) use ($currentEmployeeId) {
            return $employee['employeeID'] === $currentEmployeeId;
        });

        if (empty($employeeExists)) {
            return $this->container->getTwig()->render('@ModuleAutoUpgrade/hooks/external-layout.html.twig', $this->getParams());
        }

        return '';
    }

    /**
     * @throws \PrestaShop\Module\AutoUpgrade\Exceptions\DistributionApiException
     * @throws \PrestaShop\Module\AutoUpgrade\Exceptions\UpgradeException
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

        (new UpdateNotificationService)->setUpdateNotificationConfiguration($this->updateNotificationConfiguration);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \PrestaShop\Module\AutoUpgrade\Exceptions\UpgradeException
     */
    private function getParams(): array
    {
        $psVersion = $this->container->getProperty(UpgradeContainer::PS_VERSION);
        $psClass = '';

        if (version_compare($psVersion, '1.7.8.0', '<')) {
            $psClass = 'v1-7-3-0';
        } elseif (version_compare($psVersion, '9.0.0', '<')) {
            $psClass = 'v1-7-8-0';
        }

        $onlineVersion = $this->updateNotificationConfiguration->getVersion();

        $updateType = VersionUtils::getUpdateType($psVersion, $onlineVersion);

        return [
            'external_parent_id' => PageSelectors::EXTERNAL_PARENT_ID,
            'component' => 'dialog-update-notification',
            'version_class' => $psClass,
            'version_type' => $updateType,
            'version' => $onlineVersion,
            'contact_expert_url' => 'https://experts.prestashop.com/english/experts/',
            'update_link' => \Context::getContext()->link->getAdminLink('AdminSelfUpgrade') . '&route=' . Routes::UPDATE_PAGE_VERSION_CHOICE,
            'release_note' => $this->updateNotificationConfiguration->getReleaseNote(),
        ];
    }
}
