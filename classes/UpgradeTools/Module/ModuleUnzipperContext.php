<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools\Module;

use LogicException;

class ModuleUnzipperContext
{
    /** @var string */
    private $zipFullPath;

    /** @var string */
    private $moduleName;

    public function __construct(string $zipFullPath, string $moduleName)
    {
        $this->zipFullPath = $zipFullPath;
        $this->moduleName = $moduleName;
        $this->validate();
    }

    /**
     * @throws LogicException
     */
    private function validate(): void
    {
        if (empty($this->zipFullPath)) {
            throw new LogicException('Path to zip file is invalid.');
        }
        if (empty($this->moduleName)) {
            throw new LogicException('Module name is invalid.');
        }
    }

    public function getDestinationFilePath(): string
    {
        return $this->zipFullPath;
    }

    public function getModuleName(): string
    {
        return $this->moduleName;
    }
}
