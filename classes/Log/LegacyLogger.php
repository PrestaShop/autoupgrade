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
    protected $normalMessages = [];
    protected $severeMessages = [];
    protected $lastInfo = '';

    /**
     * @var resource|false|null File descriptor of the log file
     */
    protected $fd;

    public function __construct($fileName = null)
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
     */
    public function getErrors()
    {
        return $this->severeMessages;
    }

    /**
     * {@inheritdoc}
     */
    public function getInfos()
    {
        return $this->normalMessages;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastInfo()
    {
        return $this->lastInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = [])
    {
        if (empty($message)) {
            return;
        }

        if (is_resource($this->fd)) {
            fwrite($this->fd, '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL);
        }

        // Specific case for INFO
        if ($level === self::INFO) {
            // If last info is already defined, move it to the messages list
            if (!empty($this->lastInfo)) {
                $this->normalMessages[] = $this->lastInfo;
            }
            $this->lastInfo = $message;

            return;
        }

        if ($level < self::ERROR) {
            $this->normalMessages[] = $message;
        } else {
            $this->severeMessages[] = $message;
        }
    }
}
