<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Controller;

use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;
use Twig_Environment;

abstract class AbstractGlobalController
{
    /** @var UpgradeContainer */
    protected $upgradeContainer;

    /** @var Request */
    protected $request;

    public function __construct(UpgradeContainer $upgradeContainer, Request $request)
    {
        $this->upgradeContainer = $upgradeContainer;
        $this->request = $request;
    }

    /**
     * @return Twig_Environment|Environment
     */
    protected function getTwig()
    {
        return $this->upgradeContainer->getTwig();
    }

    protected function redirectTo(string $destinationRoute): RedirectResponse
    {
        return new RedirectResponse($this->upgradeContainer->getUrlGenerator()->getUrlToRoute($this->request, $destinationRoute));
    }
}
