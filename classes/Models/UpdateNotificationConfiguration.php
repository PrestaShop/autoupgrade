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

use PrestaShop\Module\AutoUpgrade\Hooks\DisplayBackOfficeHeader;

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
     * @var array<array{employeeID: int, timestamp?: int, versionChecked?: string}>
     */
    private $employees = [];

    /**
     * @param array{lastCheck: array{timestamp: int|null, version: string|null, releaseNote: string|null}, employees: array<array{employeeID: int, timestamp: int}>}|null $configuration
     */
    public function __construct(array $configuration = null)
    {
        if ($configuration) {
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

            $this->employees = $configuration['employees'];
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

    public function setReleaseNote(?string $releaseNote): void
    {
        $this->releaseNote = $releaseNote;
    }

    public function getReleaseNote(): ?string
    {
        return $this->releaseNote;
    }

    /**
     * @param DisplayBackOfficeHeader::DISMISS_FORM_OPTIONS|null $timeValue
     */
    public function addEmployeeReminderChoice(int $employeeId, $timeValue = null): void
    {
        $dataTime = [];

        switch ($timeValue) {
            case DisplayBackOfficeHeader::DISMISS_FORM_OPTIONS['UNTIL_NEXT_RELEASE']:
                $dataTime['versionChecked'] = $this->getVersion();
                break;
            case DisplayBackOfficeHeader::DISMISS_FORM_OPTIONS['7_DAYS']:
                $dataTime['timestamp'] = time() + DisplayBackOfficeHeader::TIMESTAMP_7_DAYS;
                break;
            case DisplayBackOfficeHeader::DISMISS_FORM_OPTIONS['30_DAYS']:
            default:
                $dataTime['timestamp'] = time() + DisplayBackOfficeHeader::TIMESTAMP_30_DAYS;
        }

        foreach ($this->employees as &$employee) {
            if ($employee['employeeID'] === $employeeId) {
                unset($employee['versionChecked'], $employee['timestamp']);

                $employee = array_merge($employee, $dataTime);

                return;
            }
        }

        $this->employees[] = array_merge([
            'employeeID' => $employeeId,
        ], $dataTime);
    }

    /**
     * @return array<array{employeeID: int, timestamp?: int, versionChecked?: string}>
     */
    public function getEmployeesReminderChoice(): array
    {
        return $this->employees;
    }

    public function toJson(): string
    {
        return json_encode([
            'lastCheck' => [
                'timestamp' => $this->getTimestamp(),
                'version' => $this->getVersion(),
                'releaseNote' => $this->getReleaseNote(),
            ],
            'employees' => $this->getEmployeesReminderChoice(),
        ]);
    }
}
