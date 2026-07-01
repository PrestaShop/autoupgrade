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

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools\CoreUpgrader\CoreServiceStub\Stubs;

use Doctrine\DBAL\Exception\DriverException;
use PrestaShop\PrestaShop\Core\ExtraProperty\Definition\ExtraPropertyDefinition;
use PrestaShop\PrestaShop\Core\ExtraProperty\Definition\ExtraPropertyDefinitionCollection;
use PrestaShop\PrestaShop\Core\ExtraProperty\Definition\ExtraPropertyDefinitionRepositoryInterface;

/**
 * Decorates the core extra property definition repository so it tolerates a missing
 * `extra_property_definition` table during the update.
 *
 * The new core files are copied on the filesystem before the database is fully migrated.
 * The version-ordered SQL upgrade runs PHP scripts (e.g. install_ps_apiresources at 9.0.0)
 * that install modules and therefore write logs. With the new core, writing a log makes
 * ObjectModel validate its extra properties, which queries the extra_property_definition
 * table. That table is only created later in the sequence (9.2.0), so the query fails with
 * a "table not found" error and aborts the update.
 *
 * While the table is missing we return an empty collection (which ObjectModel handles
 * gracefully). Once the table has been created, the decorator transparently delegates to
 * the real repository again, so no definitions are lost for the rest of the update.
 *
 * This class is only ever referenced when the core interface it implements exists, i.e.
 * when updating to a PrestaShop version shipping the extra property feature.
 */
class FaultTolerantExtraPropertyDefinitionRepository implements ExtraPropertyDefinitionRepositoryInterface
{
    /**
     * @var ExtraPropertyDefinitionRepositoryInterface
     */
    private $decorated;

    public function __construct(ExtraPropertyDefinitionRepositoryInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function getAllDefinitions(): ExtraPropertyDefinitionCollection
    {
        try {
            return $this->decorated->getAllDefinitions();
        } catch (DriverException $e) {
            if (strpos($e->getMessage(), 'TableNotFoundException') !== false) {
                return ExtraPropertyDefinitionCollection::empty();
            }
            throw $e;
        }
    }

    public function findDefinitionByModuleAndField(string $entityName, ?string $moduleName, string $fieldName): ?ExtraPropertyDefinition
    {
        try {
            return $this->decorated->findDefinitionByModuleAndField($entityName, $moduleName, $fieldName);
        } catch (DriverException $e) {
            if (strpos($e->getMessage(), 'TableNotFoundException') !== false) {
                return null;
            }
            throw $e;
        }
    }

    public function getDefinitionById(int $id): ?ExtraPropertyDefinition
    {
        try {
            return $this->decorated->getDefinitionById($id);
        } catch (DriverException $e) {
            if (strpos($e->getMessage(), 'TableNotFoundException') !== false) {
                return null;
            }
            throw $e;
        }
    }

    public function getUnprotectedDefinitionById(int $id): ExtraPropertyDefinition
    {
        // This method must return a definition and has no safe empty fallback. It is not
        // called during the update, so we simply delegate to the real repository.
        return $this->decorated->getUnprotectedDefinitionById($id);
    }
}
