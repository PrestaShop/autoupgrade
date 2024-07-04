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

/**
 * Logger to use when the messages can be seen as soon as they are created.
 * For instance, in a CLI context.
 */
class StreamedLogger extends Logger
{
    /**
     * @var int Minimum criticity of level to display
     */
    protected $filter = self::INFO;

    /**
     * @var resource File handler of standard output
     */
    protected $out;

    /**
     * @var resource File handler of standard error
     */
    protected $err;

    /**
     * @var string
     */
    protected $lastInfo;

    public function __construct()
    {
        $this->out = fopen('php://stdout', 'w');
        $this->err = fopen('php://stderr', 'w');
    }

    public function __destruct()
    {
        fclose($this->out);
        fclose($this->err);
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

        $log = self::$levels[$level] . ' - ' . $message . PHP_EOL;

        if ($level > self::ERROR) {
            fwrite($this->err, $log);
        }

        if (!$this->isFiltered($level)) {
            fwrite($this->out, $log);
            $this->lastInfo = $log;
        }
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
    public function setFilter(int $filter): StreamedLogger
    {
        if (!array_key_exists($filter, self::$levels)) {
            throw new Exception('Unknown level ' . $filter);
        }
        $this->filter = $filter;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastInfo(): string
    {
        return $this->lastInfo;
    }
}
