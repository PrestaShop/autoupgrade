<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Controller;

use PrestaShop\Module\AutoUpgrade\AjaxResponseBuilder;
use PrestaShop\Module\AutoUpgrade\DocumentationLinks;
use PrestaShop\Module\AutoUpgrade\Twig\PageSelectors;
use Symfony\Component\HttpFoundation\JsonResponse;

class ErrorReportController extends AbstractGlobalController
{
    public function displayErrorReportModal(): JsonResponse
    {
        return AjaxResponseBuilder::hydrationResponse(
            PageSelectors::DIALOG_PARENT_ID,
            $this->getTwig()->render(
                '@ModuleAutoUpgrade/dialogs/dialog-error-report.html.twig',
                [
                    'data_transparency_link' => DocumentationLinks::getPrestashopProjectDataTransparencyUrl(),
                ]
            ),
            ['addScript' => 'send-error-report-dialog']
        );
    }
}
