<?php

namespace PrestaShop\Module\AutoUpgrade\Repository;

class LocalArchiveRepository
{
    /**
     * @var string
     */
    private $downloadPath;

    public function __construct(string $downloadPath)
    {
        $this->downloadPath = $downloadPath;
    }

    public function getZipLocalArchive(): array
    {
        return glob($this->downloadPath . DIRECTORY_SEPARATOR . '*.zip');
    }

    public function getXmlLocalArchive(): array
    {
        return glob($this->downloadPath . DIRECTORY_SEPARATOR . '*.xml');
    }

    public function hasLocalArchive(): bool
    {
        return !empty($this->getZipLocalArchive()) && !empty($this->getXmlLocalArchive());
    }
}
