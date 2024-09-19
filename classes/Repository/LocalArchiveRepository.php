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

    /**
     * @return string[]|false
     */
    public function getZipLocalArchive()
    {
        return glob($this->downloadPath . DIRECTORY_SEPARATOR . '*.zip');
    }

    /**
     * @return string[]|false
     */
    public function getXmlLocalArchive()
    {
        return glob($this->downloadPath . DIRECTORY_SEPARATOR . '*.xml');
    }

    public function hasLocalArchive(): bool
    {
        return !empty($this->getZipLocalArchive()) && !empty($this->getXmlLocalArchive());
    }
}
