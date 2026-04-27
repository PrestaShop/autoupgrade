<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

use PrestaShop\Module\AutoUpgrade\Controller\AbstractGlobalController;

class DummyController extends AbstractGlobalController
{
    public function routeThatRedirectsTo($route)
    {
        return $this->redirectTo($route);
    }
}
