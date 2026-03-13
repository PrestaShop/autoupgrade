<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\State;

use PrestaShop\Module\AutoUpgrade\Parameters\RestoreConfiguration;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;

class RestoreState extends AbstractState
{
    use ProgressTrait;

    /**
     * @var string
     */
    protected $restoreName;
    /**
     * @var string|null
     */
    protected $restoreFilesFilename;
    /**
     * @var string[]
     */
    protected $restoreDbFilenames = [];

    /**
     * Int during BackupDb, allowing the script to increent the number of different file names
     * String during step RestoreDb, which contains the file to process (Data coming from toRestoreQueryList).
     *
     * @var int Contains the SQL progress
     */
    protected $dbStep = 0;

    public function initDefault(RestoreConfiguration $restoreConfiguration): void
    {
        $this->disableSave = true;

        $this->setRestoreName($restoreConfiguration->getBackupName());
        $this->setRestoreFilesFilename(null);
        $this->setRestoreDbFilenames([]);

        $this->disableSave = false;
        $this->save();
    }

    protected function getFileNameForPersistentStorage(): string
    {
        return UpgradeFileNames::STATE_RESTORE_FILENAME;
    }

    public function getDbStep(): int
    {
        return $this->dbStep;
    }

    public function setDbStep(int $dbStep): self
    {
        $this->dbStep = $dbStep;
        $this->save();

        return $this;
    }

    public function getRestoreName(): string
    {
        return $this->restoreName;
    }

    public function setRestoreName(string $restoreName): self
    {
        $this->restoreName = $restoreName;
        $this->save();

        return $this;
    }

    public function getRestoreFilesFilename(): ?string
    {
        return $this->restoreFilesFilename;
    }

    public function setRestoreFilesFilename(?string $restoreFilesFilename): self
    {
        $this->restoreFilesFilename = $restoreFilesFilename;
        $this->save();

        return $this;
    }

    /**
     * @return string[]
     */
    public function getRestoreDbFilenames(): array
    {
        return $this->restoreDbFilenames;
    }

    /**
     * @param string[] $restoreDbFilenames
     */
    public function setRestoreDbFilenames(array $restoreDbFilenames): self
    {
        $this->restoreDbFilenames = $restoreDbFilenames;
        $this->save();

        return $this;
    }

    /**
     * Pick version from restoration file name in the format v[version]_[date]-[time]-[random]
     */
    public function getRestoreVersion(): ?string
    {
        $matches = [];
        preg_match(
            '/^V(?<version>[1-9\.]+)_/',
            $this->getRestoreName(),
            $matches
        );

        return $matches[1] ?? null;
    }
}
