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
        $files = glob($this->downloadPath . DIRECTORY_SEPARATOR . '*.zip');

        return $files ? array_map('basename', $files) : false;
    }

    /**
     * @return string[]|false
     */
    public function getXmlLocalArchive()
    {
        $files = glob($this->downloadPath . DIRECTORY_SEPARATOR . '*.xml');

        return $files ? array_map('basename', $files) : false;
    }

    public function hasLocalArchive(): bool
    {
        return !empty($this->getZipLocalArchive()) && !empty($this->getXmlLocalArchive());
    }
}
