<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Controller;

use PrestaShop\Module\AutoUpgrade\AjaxResponseBuilder;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\Router\Routes;
use Symfony\Component\HttpFoundation\JsonResponse;

class HomePageController extends AbstractPageController
{
    const FORM_FIELDS = [
        'route_choice' => 'route_choice',
    ];
    const FORM_OPTIONS = [
        'update_value' => 'update',
        'restore_value' => 'restore',
    ];

    protected function getPageTemplate(): string
    {
        return 'home';
    }

    protected function displayRouteInUrl(): ?string
    {
        return Routes::HOME_PAGE;
    }

    public function submit(): JsonResponse
    {
        $routeChoice = $this->request->request->get(self::FORM_FIELDS['route_choice']);

        if ($routeChoice === self::FORM_OPTIONS['update_value']) {
            $this->upgradeContainer->getFileStorage()->clean(UpgradeFileNames::UPDATE_CONFIG_FILENAME);

            return AjaxResponseBuilder::nextRouteResponse(Routes::UPDATE_PAGE_VERSION_CHOICE);
        }

        // if is not update is restore
        if ($this->getParams()['empty_backup']) {
            return AjaxResponseBuilder::errorResponse('You can\'t access this route because you have no backups.', 401);
        }

        $this->upgradeContainer->getFileStorage()->clean(UpgradeFileNames::RESTORE_CONFIG_FILENAME);

        return AjaxResponseBuilder::nextRouteResponse(Routes::RESTORE_PAGE_BACKUP_SELECTION);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \Exception
     */
    protected function getParams(): array
    {
        $backupFinder = $this->upgradeContainer->getBackupFinder();

        return [
            'empty_backup' => empty($backupFinder->getAvailableBackups()),
            'form_route_to_submit' => Routes::HOME_PAGE_SUBMIT_FORM,
            'form_fields' => self::FORM_FIELDS,
            'form_options' => self::FORM_OPTIONS,
        ];
    }
}
