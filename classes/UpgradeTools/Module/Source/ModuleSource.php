<?php

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source;

class ModuleSource
{
    /** @var string */
    private $name;

    /** @var string */
    private $newVersion;

    /** @var string */
    private $path;

    /** @var bool */
    private $unzipable;

    public function __construct(string $name, string $newVersion, string $path, bool $unzipable)
    {
        $this->name = $name;
        $this->newVersion = $newVersion;
        $this->path = $path;
        $this->unzipable = $unzipable;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getNewVersion(): string
    {
        return $this->newVersion;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function isZipped(): bool
    {
        return $this->unzipable;
    }

    /** @return array<string, string|boolean> */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'newVersion' => $this->newVersion,
            'path' => $this->path,
            'unzipable' => $this->unzipable,
        ];
    }
}
