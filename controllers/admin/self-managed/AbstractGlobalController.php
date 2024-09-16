<?php

namespace PrestaShop\Module\AutoUpgrade\Controller;

use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use Twig\Environment;
use Twig_Environment;

class AbstractGlobalController
{
    /** @var UpgradeContainer */
    protected $upgradeContainer;

    /** @var Twig_Environment|Environment */
    protected $twig;

    public function __construct(UpgradeContainer $upgradeContainer)
    {
        $this->upgradeContainer = $upgradeContainer;
        $this->twig = $this->upgradeContainer->getTwig();
    }
}
