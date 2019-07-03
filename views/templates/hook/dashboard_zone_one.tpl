{*
* 2007-2017 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2017 PrestaShop SA
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*}

<section id="autoupgradePhpWarn" class="panel widget">
  <div class="panel-heading">
    <span class="icon-stack text-danger">
      <span class="icon icon-circle icon-stack-2x"></span>
      <strong class="icon-stack-1x icon-stack-text">1</strong>
    </span>
    {l s='PHP Version notice' mod='autoupgrade'}


    <span class="panel-heading-action">
      <a class="list-toolbar-btn" href="{$ignore_link}" title="Ignore">
        <i class="process-icon-close"></i>
      </a>
    </span>
  </div>

  <p class="text-muted text-center">
    <i id="autoupgradePhpWarningMainIcon" class="icon-history icon-flip-horizontal"></i>
  </p>
  <span>
    <p>
      {l s='The PHP version your shop is running on is insecure.' mod='autoupgrade'}<br>
      {l s='It reached its end-of-life, which means it won\'t get security updates anymore and projects will stop supporting it.' mod='autoupgrade'} </span> <p><br>

      {l s='Upgrading will keep your shop secured and performant.' mod='autoupgrade'} </span> <p><br>
    </p>
    <div align="center">
      <a class="btn btn-primary" style="white-space: unset;" href="{$learn_more_link}" target="_blank">
          <i class="icon-external-link"></i> {l s='Learn more about PHP and upgrading' mod='autoupgrade'}
      </a>
  </div>
</section>