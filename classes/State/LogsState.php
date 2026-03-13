<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\State;

use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;

class LogsState extends AbstractState
{
    /** @var string|null */
    protected $activeBackupLogFile;

    /** @var string|null */
    protected $activeRestoreLogFile;

    /** @var string|null */
    protected $activeUpdateLogFile;

    /** @var string|null */
    protected $timeZone;

    protected function getFileNameForPersistentStorage(): string
    {
        return UpgradeFileNames::STATE_LOGS_FILENAME;
    }

    public function getActiveBackupLogFile(): ?string
    {
        return $this->activeBackupLogFile;
    }

    public function setActiveBackupLogFromDateTime(string $datetime): self
    {
        $this->activeBackupLogFile = $datetime . '-backup.txt';
        $this->save();

        return $this;
    }

    public function getActiveRestoreLogFile(): ?string
    {
        return $this->activeRestoreLogFile;
    }

    public function setActiveRestoreLogFromDateTime(string $datetime): self
    {
        $this->activeRestoreLogFile = $datetime . '-restore.txt';
        $this->save();

        return $this;
    }

    public function getActiveUpdateLogFile(): ?string
    {
        return $this->activeUpdateLogFile;
    }

    public function setActiveUpdateLogFromDateTime(string $datetime): self
    {
        $this->activeUpdateLogFile = $datetime . '-update.txt';
        $this->save();

        return $this;
    }

    public function getTimeZone(): ?string
    {
        return $this->timeZone;
    }

    public function setTimeZone(string $timeZone): self
    {
        $this->timeZone = $timeZone;
        $this->save();

        return $this;
    }
}
