<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools;

use ReflectionClass;

/**
 * TODO: Create a class for 1.7 env and another one for 1.6 ?
 */
class SymfonyAdapter
{
    public function isKernelReachable(): bool
    {
        return defined('_PS_ROOT_DIR_') && class_exists('AppKernel', true);
    }

    /**
     * Return the appropriate kernel if abstract or not.
     *
     * @return \AppKernel|\AdminKernel
     */
    public function initKernel()
    {
        global $kernel;
        if (!$kernel instanceof \AppKernel) {
            // Only necessary one version before 1.7.3 because he is not classmaped on composer
            require_once _PS_ROOT_DIR_ . '/app/AppKernel.php';
            $env = (true == _PS_MODE_DEV_) ? 'dev' : 'prod';

            // From version 9 the AppKernel becomes an abstract so we need to check if it is one to know which Kernel to use
            if ($this->isAppKernelAbstract()) {
                $kernelClass = 'AdminKernel';
            } else {
                $kernelClass = 'AppKernel';
            }

            $kernel = new $kernelClass($env, _PS_MODE_DEV_);
            if (method_exists($kernel, 'loadClassCache')) { // This method has been deleted in Symfony 4.x
                $kernel->loadClassCache();
            }

            $kernel->boot();
        }

        return $kernel;
    }

    /**
     * Check if AppKernel is abstract or not.
     */
    private function isAppKernelAbstract(): bool
    {
        $appKernelClass = new ReflectionClass(\AppKernel::class);

        return $appKernelClass->isAbstract();
    }
}
