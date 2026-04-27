<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Task;

use Exception;
use PrestaShop\Module\AutoUpgrade\AjaxResponse;
use PrestaShop\Module\AutoUpgrade\Analytics;
use PrestaShop\Module\AutoUpgrade\Exceptions\ProcessException;
use PrestaShop\Module\AutoUpgrade\Log\Logger;
use PrestaShop\Module\AutoUpgrade\Task\Runner\ChainedTasks;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;
use ReflectionClass;

abstract class AbstractTask
{
    /**
     * usage :  key = the step you want to skip
     *          value = the next step you want instead
     * example : public static $skipAction = array();
     *
     * @var array<string, string>
     */
    public static $skipAction = [];

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @var UpgradeContainer
     */
    protected $container;

    /**
     * @var TaskType::TASK_TYPE_*|null
     */
    const TASK_TYPE = null;

    // Task progress details
    /**
     * @var bool
     */
    protected $stepDone;
    /**
     * @var string
     */
    protected $status;
    /**
     * @var bool
     */
    protected $error;
    /**
     * @var array<string, string|array<string>>
     */
    protected $nextParams = [];
    /**
     * @var TaskName::TASK_*|null
     */
    protected $next;

    /**
     * @throws Exception
     */
    public function __construct(UpgradeContainer $container)
    {
        $this->container = $container;
        $this->logger = $this->container->getLogger();
        $this->translator = $this->container->getTranslator();
        $this->checkTaskMayRun();

        if ($this::TASK_TYPE !== null) {
            $logPath = $this->container->getLogsService()->getLogsPath($this::TASK_TYPE);
            if ($logPath !== null) {
                $this->logger->updateLogsPath($logPath);
            }
        }
    }

    /**
     * @return string Json encoded data from AjaxResponse
     */
    public function getJsonResponse(): string
    {
        return $this->getResponse()->getJson();
    }

    /**
     * Get result of the task and data to send to the next request.
     *
     * @return AjaxResponse
     */
    public function getResponse(): AjaxResponse
    {
        $response = new AjaxResponse(
            $this->container->getStateFromTaskType($this->getTaskType()),
            $this->logger
        );

        return $response->setError($this->error)
            ->setStepDone($this->stepDone)
            ->setNext($this->next)
            ->setNextParams($this->nextParams)
            ->setUpdateConfiguration($this->container->getUpdateConfiguration());
    }

    public function setErrorFlag(): void
    {
        $this->error = true;
        // TODO: Add this? $this->next = 'error';

        if (static::TASK_TYPE) {
            $propertiesType = null;

            switch (static::TASK_TYPE) {
                case TaskType::TASK_TYPE_UPDATE:
                    $propertiesType = Analytics::WITH_UPDATE_PROPERTIES;
                    break;
                case TaskType::TASK_TYPE_BACKUP:
                    $propertiesType = Analytics::WITH_BACKUP_PROPERTIES;
                    break;
                case TaskType::TASK_TYPE_RESTORE:
                    $propertiesType = Analytics::WITH_RESTORE_PROPERTIES;
                    break;
            }

            $this->container->getAnalytics()->track(
                ucfirst(static::TASK_TYPE) . ' Failed',
                $propertiesType,
                ['failing_step' => (new ReflectionClass($this))->getShortName()]
            );
        }
    }

    public function init(): void
    {
    }

    abstract public function run(): int;

    protected function handleException(ProcessException $e): void
    {
        foreach ($e->getQuickInfos() as $log) {
            $this->logger->warning($log);
        }

        if ($e->getSeverity() === ProcessException::SEVERITY_ERROR) {
            $this->next = TaskName::TASK_ERROR;
            $this->setErrorFlag();
            $this->logger->error($e->getMessage());
        }
        if ($e->getSeverity() === ProcessException::SEVERITY_WARNING) {
            $this->logger->warning($e->getMessage());
            $this->container->getUpdateState()->setWarningDetected(true);
        }
    }

    /**
     * @return TaskType::TASK_TYPE_* $task
     */
    private function getTaskType(): string
    {
        if ($this instanceof ChainedTasks) {
            return $this->stepClass::TASK_TYPE;
        }

        return $this::TASK_TYPE;
    }

    private function checkTaskMayRun(): void
    {
        // PrestaShop demo mode
        if (defined('_PS_MODE_DEMO_') && _PS_MODE_DEMO_ == true) {
            return;
        }

        $currentAction = get_class($this);
        if (isset(self::$skipAction[$currentAction])) {
            $this->next = self::$skipAction[$currentAction];
            $this->logger->info($this->translator->trans('Action %s skipped', [$currentAction]));
        }
    }
}
