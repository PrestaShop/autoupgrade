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

/**
 * This class reimplement the old properties of the class AdminSelfUpgrade,
 * where all the mesages were stored.
 */
class LegacyLogger extends Logger
{
    /** @var string[] */
    protected $normalMessages = [];

    /** @var string[] */
    protected $severeMessages = [];

    /** @var string */
    protected $lastInfo = '';

    /** @var array<string, string> */
    protected $sensitiveData = [];

    /**
     * @var resource|false|null File descriptor of the log file
     */
    protected $fd;

    public function __construct(string $fileName = null)
    {
        if (null !== $fileName) {
            $this->fd = fopen($fileName, 'a');
        }
    }

    public function __destruct()
    {
        if (is_resource($this->fd)) {
            fclose($this->fd);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->severeMessages;
    }

    /**
     * {@inheritdoc}
     *
     * @return string[]
     */
    public function getInfos(): array
    {
        return $this->normalMessages;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastInfo(): string
    {
        return $this->lastInfo;
    }

    /**
     * @param array<string, string> $sensitiveData List of data to change with another value
     */
    public function setSensitiveData(array $sensitiveData): self
    {
        $this->sensitiveData = $sensitiveData;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param array<mixed> $context
     */
    public function log($level, string $message, array $context = []): void
    {
        if (empty($message)) {
            return;
        }

        $message = $this->cleanFromSensitiveData($message);

        if (is_resource($this->fd)) {
            fwrite($this->fd, '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL);
        }

        if ($level === self::INFO) {
            $this->lastInfo = $message;
        }

        if ($level < self::ERROR) {
            $this->normalMessages[] = $message;
        } else {
            $this->severeMessages[] = $message;
        }
    }

    public function cleanFromSensitiveData(string $message): string
    {
        if (empty($this->sensitiveData)) {
            return $message;
        }

        return str_replace(
            array_keys($this->sensitiveData),
            array_values($this->sensitiveData),
            $message
        );
    }
}
