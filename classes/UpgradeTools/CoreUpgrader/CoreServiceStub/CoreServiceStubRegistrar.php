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
 *
 * To add a new stub:
 *  - create the stub class in the Stubs/ subfolder (a decorator implementing the core
 *    contract is the usual shape), exposing a static register(ContainerInterface) method;
 *  - add an entry below mapping the core symbol that must exist for the stub to be relevant
 *    to the callable installing it.
 *
 * The map is keyed by a core class/interface name kept as a literal string: the existence
 * check runs before the stub class is referenced, so stub classes that implement a core
 * contract are never autoloaded against a core version that does not ship it.
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
        foreach ($this->getStubs() as $requiredCoreSymbol => $register) {
            if (!interface_exists($requiredCoreSymbol) && !class_exists($requiredCoreSymbol)) {
                continue;
            }

            try {
                $register($container);
            } catch (Throwable $e) {
                $this->logger->warning($this->translator->trans('Unable to register the core service stub for %s during the update: %s', [$requiredCoreSymbol, $e->getMessage()]));
            }
        }
    }

    /**
     * @return array<string, callable(ContainerInterface):void> core symbol => stub installer
     */
    private function getStubs(): array
    {
        return [
            // PrestaShop 9.2+: the extra_property_definition table is created late in the
            // migration, after PHP scripts that already make ObjectModel query it.
            'PrestaShop\PrestaShop\Core\ExtraProperty\Definition\ExtraPropertyDefinitionRepositoryInterface' => [FaultTolerantExtraPropertyDefinitionRepository::class, 'register'],
        ];
    }
}
