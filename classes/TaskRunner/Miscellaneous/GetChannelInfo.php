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

namespace PrestaShop\Module\AutoUpgrade\TaskRunner\Miscellaneous;

use PrestaShop\Module\AutoUpgrade\ChannelInfo;
use PrestaShop\Module\AutoUpgrade\TaskRunner\AbstractTask;
use PrestaShop\Module\AutoUpgrade\Twig\Block\ChannelInfoBlock;

/**
 * display informations related to the selected channel : link/changelog for remote channel,
 * or configuration values for special channels
 */
class GetChannelInfo extends AbstractTask
{
    public function run()
    {
        // do nothing after this request (see javascript function doAjaxRequest )
        $this->upgradeClass->next = '';

        $channel = $this->upgradeClass->currentParams['channel'];
        $channelInfo = (new ChannelInfo($this->container->getUpgrader(), $this->container->getUpgradeConfiguration(), $channel));
        $channelInfoArray = $channelInfo->getInfo();
        $this->upgradeClass->nextParams['result']['available'] = $channelInfoArray['available'];

        $this->upgradeClass->nextParams['result']['div'] = (new ChannelInfoBlock(
            $this->container->getUpgradeConfiguration(),
            $channelInfo,
            $this->container->getTwig(),
            $this->translator)
        )->render();
    }
}