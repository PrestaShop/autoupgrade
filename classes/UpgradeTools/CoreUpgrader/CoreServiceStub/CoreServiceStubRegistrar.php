<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools\CoreUpgrader\CoreServiceStub;

use PrestaShop\Module\AutoUpgrade\Log\LoggerInterface;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\CoreUpgrader\CoreServiceStub\Stubs\FaultTolerantExtraPropertyDefinitionRepository;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;
use ReflectionObject;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Throwable;

/**
 * Replaces some core services with fault-tolerant stubs during the database update.
 *
 * While the new core files are already on the filesystem, the database is still being
 * migrated and may lack tables or columns the new code expects. A core service queried by
 * an early upgrade step can therefore fail on a schema that is not complete yet. For each
 * such case we register a stub that tolerates the incomplete schema until the migration
 * catches up.
 */
class CoreServiceStubRegistrar
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Translator
     */
    private $translator;

    public function __construct(LoggerInterface $logger, Translator $translator)
    {
        $this->logger = $logger;
        $this->translator = $translator;
    }

    public function register(ContainerInterface $container): void
    {
        foreach ($this->getStubs() as $requiredCoreSymbol => $stubClass) {
            if (!interface_exists($requiredCoreSymbol) && !class_exists($requiredCoreSymbol)) {
                continue;
            }

            if (!$container->has($requiredCoreSymbol)) {
                continue;
            }

            try {
                $decorated = $container->get($requiredCoreSymbol);
                $stub = new $stubClass($decorated);

                try {
                    $container->set($requiredCoreSymbol, $stub);
                } catch (Throwable $e) {
                    // Fetching $decorated just above already resolved and cached the service,
                    // so the container now considers it initialized and refuses a plain set() on
                    // it ("already initialized"). Force the swap directly on the container's
                    // internal service cache instead, so later callers in this same request
                    // (ObjectModel in particular) get our stub rather than the raw service.
                    $this->forceReplace($container, $requiredCoreSymbol, $stub);
                }
            } catch (Throwable $e) {
                $this->logger->warning($this->translator->trans('Unable to register the core service stub for %s during the update: %s', [$requiredCoreSymbol, $e->getMessage()]));
            }
        }
    }

    /**
     * @return array<string, class-string> core symbol => decorating stub class
     */
    private function getStubs(): array
    {
        return [
            // PrestaShop 9.2+: the extra_property_definition table is created late in the
            // migration, after PHP scripts that already make ObjectModel query it.
            'PrestaShop\PrestaShop\Core\ExtraProperty\Definition\ExtraPropertyDefinitionRepositoryInterface' => FaultTolerantExtraPropertyDefinitionRepository::class,
        ];
    }

    /**
     * @param mixed $service
     */
    private function forceReplace(ContainerInterface $container, string $id, $service): void
    {
        $reflectionClass = new ReflectionObject($container);
        while ($reflectionClass && !$reflectionClass->hasProperty('services')) {
            $reflectionClass = $reflectionClass->getParentClass();
        }

        if (!$reflectionClass) {
            throw new RuntimeException(sprintf('Cannot override the "%s" service: cound\'t reach the container "%s".', $id, get_class($container)));
        }

        $servicesProperty = $reflectionClass->getProperty('services');
        $servicesProperty->setAccessible(true);
        $services = $servicesProperty->getValue($container);
        $services[$id] = $service;
        $servicesProperty->setValue($container, $services);
    }
}
