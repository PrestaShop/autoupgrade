<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Controller;

use PrestaShop\Module\AutoUpgrade\AjaxResponseBuilder;
use PrestaShop\Module\AutoUpgrade\Twig\PageSelectors;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractPageWithStepController extends AbstractPageController
{
    public function step(): Response
    {
        if (!$this->request->isXmlHttpRequest()) {
            return new Response('Unexpected call to a step route outside an ajax call.', 404);
        }

        // It may be tempting to move this line inside the parameters of the method
        // `getTwig()->render()`. Please refrain to do so as this makes Twig
        // called BEFORE the call to the function sent as parameters. Initiating it too early
        // can be misleading when rendering the templates as more autoloaders can be loaded
        // in the meantime (i.e the core).
        $params = $this->getParams();

        return AjaxResponseBuilder::hydrationResponse(
            PageSelectors::STEP_PARENT_ID,
            $this->getTwig()->render(
                '@ModuleAutoUpgrade/steps/' . $this->getStepTemplate() . '.html.twig',
                $params
            ),
            ['newRoute' => $this->displayRouteInUrl()]
        );
    }

    /**
     * Relative path from the templates folder of the twig file
     * to load when reaching this step.
     *
     * @see step()
     *
     * @return string
     */
    abstract protected function getStepTemplate(): string;
}
