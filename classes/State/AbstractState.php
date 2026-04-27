<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\State;

use PrestaShop\Module\AutoUpgrade\Parameters\FileStorage;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;

/**
 * The State is used to keep track of the remaining operations to do on the shop during a process.
 * Its lifespan is strictly linked to a running process, it has no use outside it.
 *
 * @see PrestaShop\Module\AutoUpgrade\Parameters\UpdateConfiguration to prepare data that will be needed during an update.
 */
abstract class AbstractState
{
    /** @var bool */
    protected $disableSave = false;

    /** @var FileStorage */
    private $fileStorage;

    public function __construct(FileStorage $fileStorage)
    {
        $this->fileStorage = $fileStorage;
    }

    /** @return UpgradeFileNames::STATE_*_FILENAME */
    abstract protected function getFileNameForPersistentStorage(): string;

    /**
     * @return void
     */
    public function load()
    {
        $state = $this->fileStorage->load($this->getFileNameForPersistentStorage());

        $this->importFromArray($state);
    }

    /**
     * @param array<string, mixed> $savedState from another request
     */
    public function importFromArray(array $savedState): self
    {
        foreach ($savedState as $name => $value) {
            if (!empty($value) && property_exists($this, $name)) {
                $this->{$name} = $value;
            }
        }

        return $this;
    }

    public function importFromEncodedData(string $encodedData): self
    {
        $decodedData = json_decode(base64_decode($encodedData), true);
        if (empty($decodedData['nextParams'])) {
            return $this;
        }

        return $this->importFromArray($decodedData['nextParams']);
    }

    public function save(): bool
    {
        if (!$this->disableSave) {
            return $this->fileStorage->save($this->export(), $this->getFileNameForPersistentStorage());
        }

        return true;
    }

    /**
     * @return array<string, mixed> of class properties for export
     */
    public function export(): array
    {
        $state = get_object_vars($this);
        foreach (['fileStorage', 'disableSave'] as $keyToRemove) {
            unset($state[$keyToRemove]);
        }

        return $state;
    }

    public function isInitialized(): bool
    {
        return $this->fileStorage->exists($this->getFileNameForPersistentStorage());
    }
}
