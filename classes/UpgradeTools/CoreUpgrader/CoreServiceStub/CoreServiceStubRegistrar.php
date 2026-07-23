<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
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
                $stub = new $stubClass($decorated, $this->logger);

                // Overriding the service with $container->set() will always fail because we just called it.
                // We must force it.
                $this->forceReplace($container, $requiredCoreSymbol, $stub);
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
