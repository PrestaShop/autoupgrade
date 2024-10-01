<?php

use PrestaShop\Module\AutoUpgrade\Controller\AbstractGlobalController;

class DummyController extends AbstractGlobalController
{
    public function routeThatRedirectsTo($route)
    {
        return $this->redirectTo($route);
    }
}
