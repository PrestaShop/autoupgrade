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

namespace PrestaShop\Module\AutoUpgrade\Log;

use Exception;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Logger for a command line interface, allowing formatting of logs
 * Messages can be seen as soon as they are created.
 */
class CliLogger extends Logger
{
    /**
     * @var int Minimum criticity of level to display
     */
    protected $filter = self::INFO;

    /**
     * @var OutputInterface Standard output
     */
    protected $out;

    /**
     * @var OutputInterface Error output
     */
    protected $err;

    /**
     * @var ?string
     */
    protected $lastInfo;

    public function __construct(OutputInterface $output)
    {
        if ($output->isDecorated()) {
            $successStyle = new OutputFormatterStyle('green', null, []);
            $warningStyle = new OutputFormatterStyle('yellow', null, []);
            $errorStyle = new OutputFormatterStyle('red', null, []);
            $criticalStyle = new OutputFormatterStyle('red', null, ['bold']);
            $output->getFormatter()->setStyle('success', $successStyle);
            $output->getFormatter()->setStyle('warning', $warningStyle);
            $output->getFormatter()->setStyle('error', $errorStyle);
            $output->getFormatter()->setStyle('critical', $criticalStyle);
        }

        $this->out = $output;
        $this->err = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;
    }

    /**
     * Check the verbosity allows the message to be displayed.
     *
     * @param int $level
     *
     * @return bool
     */
    public function isFiltered(int $level): bool
    {
        return $level < $this->filter;
    }

    public function getFilter(): int
    {
        return $this->filter;
    }

    /**
     * Set the verbosity of the logger.
     *
     * @throws Exception
     */
    public function setFilter(int $filter): CliLogger
    {
        if (!array_key_exists($filter, self::$levels)) {
            throw new Exception('Unknown level ' . $filter);
        }
        $this->filter = $filter;

        return $this;
    }

    /**
     * {@inherit}.
     *
     * @param array<mixed> $context
     */
    public function log($level, string $message, array $context = []): void
    {
        if (empty($message)) {
            return;
        }

        $log = $this->formatLog($level, $message);

        if ($level > self::ERROR) {
            $this->err->writeln($log);
        }

        if (!$this->isFiltered($level)) {
            $this->out->writeln($log);
            if ($level === self::INFO) {
                $this->lastInfo = $log;
            }
        }

        $message = $this->cleanFromSensitiveData($message);
        parent::log($level, $message, $context);
    }

    private function formatLog(int $level, string $message): string
    {
        $logBase = self::$levels[$level] . ' - ' . $message;

        if (!$this->out->isDecorated()) {
            return $logBase;
        }

        switch ($level) {
            case self::WARNING:
                return '<warning>' . $logBase . '</warning>';
            case self::ERROR:
                return '<error>' . $logBase . '</error>';
            case self::CRITICAL:
            case self::ALERT:
            case self::EMERGENCY:
             return '<critical>' . $logBase . '</critical>';
            default:
                return $logBase;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getLastInfo(): ?string
    {
        return $this->lastInfo;
    }
}
