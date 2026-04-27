<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Task\Update;

use Exception;
use PrestaShop\Module\AutoUpgrade\Task\AbstractTask;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\Task\TaskName;
use PrestaShop\Module\AutoUpgrade\Task\TaskType;

/**
 * Clean the database from unwanted entries.
 */
class CleanDatabase extends AbstractTask
{
    const TASK_TYPE = TaskType::TASK_TYPE_UPDATE;

    public function run(): int
    {
        $this->container->getUpdateState()->setProgressPercentage(
            $this->container->getCompletionCalculator()->getBasePercentageOfTask(self::class)
        );

        // Clean tabs order
        foreach ($this->container->getDb()->ExecuteS('SELECT DISTINCT id_parent FROM ' . _DB_PREFIX_ . 'tab') as $parent) {
            $i = 1;
            foreach ($this->container->getDb()->ExecuteS('SELECT id_tab FROM ' . _DB_PREFIX_ . 'tab WHERE id_parent = ' . (int) $parent['id_parent'] . ' ORDER BY IF(class_name IN ("AdminHome", "AdminDashboard"), 1, 2), position ASC') as $child) {
                $this->container->getDb()->Execute('UPDATE ' . _DB_PREFIX_ . 'tab SET position = ' . (int) ($i++) . ' WHERE id_tab = ' . (int) $child['id_tab'] . ' AND id_parent = ' . (int) $parent['id_parent']);
            }
        }

        $this->status = 'ok';
        $this->next = TaskName::TASK_UPDATE_COMPLETE;
        $this->logger->info($this->translator->trans('The database has been cleaned.'));

        return ExitCode::SUCCESS;
    }

    /**
     * @throws Exception
     */
    public function init(): void
    {
        $this->container->initPrestaShopCore();
    }
}
