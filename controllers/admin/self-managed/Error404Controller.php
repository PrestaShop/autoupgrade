<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Controller;

use Symfony\Component\HttpFoundation\Response;

class Error404Controller extends AbstractPageController
{
    public function index()
    {
        $response = parent::index();

        if ($response instanceof Response) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
        } else {
            http_response_code(Response::HTTP_NOT_FOUND);
        }

        return $response;
    }

    protected function getPageTemplate(): string
    {
        return 'errors/' . Response::HTTP_NOT_FOUND;
    }

    protected function getParams(): array
    {
        return [
            // TODO: assets_base_path is provided by all controllers. What about a asset() twig function instead?
            'assets_base_path' => $this->upgradeContainer->getAssetsEnvironment()->getAssetsBaseUrl($this->request),

            'error_code' => Response::HTTP_NOT_FOUND,
        ];
    }
}
