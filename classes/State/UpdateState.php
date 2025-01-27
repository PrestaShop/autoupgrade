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

namespace PrestaShop\Module\AutoUpgrade\State;

use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfiguration;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\Upgrader;

class UpdateState extends AbstractState
{
    use ProgressTrait;

    /**
     * Origin version of PrestaShop
     *
     * @var string
     */
    protected $currentVersion;
    /**
     * Destination version of PrestaShop
     *
     * @var ?string
     */
    protected $destinationVersion;

    /**
     * installedLanguagesIso is an array of iso_code of each installed languages.
     *
     * @var string[]
     */
    protected $installedLanguagesIso = [];

    /**
     * @var bool Determining if all steps went totally successfully
     */
    protected $warningDetected = false;

    protected function getFileNameForPersistentStorage(): string
    {
        return UpgradeFileNames::STATE_UPDATE_FILENAME;
    }

    public function initDefault(string $currentVersion, Upgrader $upgrader, UpgradeConfiguration $updateConfiguration): void
    {
        $this->disableSave = true;
        $this->setInstalledLanguagesIso($updateConfiguration->getInstalledLanguagesIsoCode());

        $this->setCurrentVersion($currentVersion);
        $this->setDestinationVersion($upgrader->getDestinationVersion());
        $this->disableSave = false;
        $this->save();
    }

    public function getCurrentVersion(): string
    {
        return $this->currentVersion;
    }

    public function setCurrentVersion(string $currentVersion): self
    {
        $this->currentVersion = $currentVersion;
        $this->save();

        return $this;
    }

    public function getDestinationVersion(): ?string
    {
        return $this->destinationVersion;
    }

    public function setDestinationVersion(?string $destinationVersion): self
    {
        $this->destinationVersion = $destinationVersion;
        $this->save();

        return $this;
    }

    /** @return string[] */
    public function getInstalledLanguagesIso(): array
    {
        return $this->installedLanguagesIso;
    }

    /**
     * @param string[] $installedLanguagesIso
     */
    public function setInstalledLanguagesIso(array $installedLanguagesIso): self
    {
        $this->installedLanguagesIso = $installedLanguagesIso;
        $this->save();

        return $this;
    }

    public function isWarningDetected(): bool
    {
        return $this->warningDetected;
    }

    public function setWarningDetected(bool $warningDetected): self
    {
        $this->warningDetected = $warningDetected;
        $this->save();

        return $this;
    }
}
