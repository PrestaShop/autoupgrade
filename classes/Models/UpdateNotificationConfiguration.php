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

namespace PrestaShop\Module\AutoUpgrade\Models;

class UpdateNotificationConfiguration
{
    /**
     * @var int|null
     */
    private $timestamp = null;

    /**
     * @var string|null
     */
    private $version = null;

    /**
     * @var string|null
     */
    private $releaseNote = null;

    /**
     * @var array<array{employeeID: int, timestamp: int}>
     */
    private $employees = [];

    public function __construct($configuration = null)
    {
        if ($configuration) {
            if (isset($configuration['lastCheck'])) {
                $lastCheck = $configuration['lastCheck'];

                if (isset($lastCheck['timestamp'])) {
                    $this->timestamp = $lastCheck['timestamp'];
                }

                if (isset($lastCheck['version'])) {
                    $this->version = $lastCheck['version'];
                }

                if (isset($lastCheck['releaseNote'])) {
                    $this->releaseNote = $lastCheck['releaseNote'];
                }
            }

            if (isset($configuration['employees'])) {
                $this->employees = $configuration['employees'];
            }
        } else {
        }
    }

    public function setTimestamp(int $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    public function getTimestamp(): ?int
    {
        return $this->timestamp;
    }

    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setReleaseNote(string $releaseNote): void
    {
        $this->releaseNote = $releaseNote;
    }

    public function getReleaseNote(): ?string
    {
        return $this->releaseNote;
    }

    public function addEmployee(int $employeeId, int $timestamp): void
    {
        foreach ($this->employees as &$employee) {
            if ($employee['employeeID'] === $employeeId) {
                $employee['timestamp'] = $timestamp;
                return;
            }
        }

        $this->employees[] = [
            'employeeID' => $employeeId,
            'timestamp' => $timestamp,
        ];
    }

    /**
     * @return array<array{employeeID: int, timestamp: int}>
     */
    public function getEmployees(): array
    {
        return $this->employees;
    }

    public function toJson()
    {
        return json_encode([
            'lastCheck' => [
                'timestamp' => $this->getTimestamp(),
                'version' => $this->getVersion(),
                'releaseNote' => $this->getReleaseNote(),
            ],
            'employees' => $this->getEmployees(),
        ]);
    }
}
