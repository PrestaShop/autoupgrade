<?php
/*
 * 2007-2018 PrestaShop
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 * 
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2018 PrestaShop SA
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\Module\AutoUpgrade\TaskRunner;

use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use Psr\Log\LoggerInterface;

abstract class AbstractTask
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var \PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator
     */
    protected $translator;

    /**
     * @var UpgradeContainer
     */
    protected $container;

    // Task progress details
    protected $stepDone = true;
    protected $status = true;
    protected $error = false;
    protected $nextParams = array();
    protected $next = 'N/A';

    public function __construct(UpgradeContainer $container)
    {
        $this->container = $container;
        $this->logger = $this->container->getLogger();
        $this->translator = $this->container->getTranslator();
        $this->checkTaskMayRun();
    }

    public function getAjaxResponse()
    {
        $response = new AjaxResponse($this->container->getTranslator(), $this->container->getState(), $this->upgradeContainer->getLogger());
        return $response->setError($this->error)
            ->setStepDone($this->stepDone)
            ->setNext($this->next)
            ->setNextParams($this->nextParams)
            ->setUpgradeConfiguration($this->container->getUpgradeConfiguration())
            ->getJsonResponse();
    }

    private function checkTaskMayRun()
    {
        /* PrestaShop demo mode */
        if (defined('_PS_MODE_DEMO_') && _PS_MODE_DEMO_) {
            return;
        }

        if (!empty($_POST['action'])) {
            $action = $_POST['action'];
            if (isset(\AdminSelfUpgrade::$skipAction[$action])) {
                $this->next = \AdminSelfUpgrade::$skipAction[$action];
                $this->logger->info($this->translator->trans('Action %s skipped', array($action), 'Modules.Autoupgrade.Admin'));
                unset($_POST['action']);
            }
        }
    }

    abstract public function run();
}