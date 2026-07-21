<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools;

use PrestaShop\Module\AutoUpgrade\UpgradeTools\CoreUpgrader\CoreServiceStub\CoreServiceStubRegistrar;
use ReflectionClass;

/**
 * TODO: Create a class for 1.7 env and another one for 1.6 ?
 */
class SymfonyAdapter
{
    /** @var CoreServiceStubRegistrar */
    private $coreServiceStubRegistrar;

    public function __construct(
        CoreServiceStubRegistrar $coreServiceStubRegistrar
    ) {
        $this->coreServiceStubRegistrar = $coreServiceStubRegistrar;
    }

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
            // Starting from PrestaShop 9, some parts of the new context are defined by event listeners on kernel.request.
            // We may have to trigger it with dummy data in the future.

            // The destination core files run against a database that may be not fully migrated yet:
            // replace the core services that would fail on the incomplete schema with stubs.
            $this->coreServiceStubRegistrar->register($kernel->getContainer());
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
