<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\State;

use PrestaShop\Module\AutoUpgrade\Parameters\UpdateConfiguration;
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
     * @var bool Determining if we can proceed to uninstall module step (only if destination version is know by Distribution API)
     */
    protected $skipUninstallModule = false;

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

    public function initDefault(string $currentVersion, Upgrader $upgrader, UpdateConfiguration $updateConfiguration): void
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

    public function getSkipUninstallModule(): bool
    {
        return $this->skipUninstallModule;
    }

    public function setSkipUninstallModule(bool $skipUninstallModule): self
    {
        $this->skipUninstallModule = $skipUninstallModule;
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
