<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools\Module;

use LogicException;
use PrestaShop\Module\AutoUpgrade\Exceptions\ProcessException;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;
use PrestaShop\Module\AutoUpgrade\ZipAction;
use Symfony\Component\Filesystem\Filesystem;

class ModuleUnzipper
{
    /** @var Translator */
    private $translator;

    /** @var ZipAction */
    private $zipAction;

    /** @var string */
    private $modulesFolder;

    public function __construct(Translator $translator, ZipAction $zipAction, string $modulesFolder)
    {
        $this->translator = $translator;
        $this->zipAction = $zipAction;
        $this->modulesFolder = $modulesFolder;
    }

    /**
     * @throws LogicException|ProcessException
     */
    public function unzipModule(ModuleUnzipperContext $moduleUnzipperContext): void
    {
        $updatedModulePath = $moduleUnzipperContext->getDestinationFilePath();

        if (is_file($updatedModulePath) && !$this->zipAction->extract($updatedModulePath, $this->modulesFolder)) {
            throw (new ProcessException($this->translator->trans('Error when trying to extract module %s.', [$moduleUnzipperContext->getModuleName()])))->setSeverity(ProcessException::SEVERITY_WARNING);
        }

        // Module is already unzipped, we make the actual move in the modules folder.
        if (is_dir($updatedModulePath)) {
            $filesystem = new Filesystem();
            $filesystem->mirror($updatedModulePath, $this->modulesFolder . DIRECTORY_SEPARATOR . $moduleUnzipperContext->getModuleName());
        }
    }
}
