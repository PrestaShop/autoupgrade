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
            // WHY: 'override' is required, not cosmetic. Without it mirror() copies a file only when
            // the source is strictly newer, because Filesystem::copy() compares modification times.
            // An archive that preserves the timestamps of its contents, or a source produced by a
            // copy that kept them, therefore leaves the installed file untouched - the module is
            // reported as updated while still running its previous code. Measured on
            // symfony/filesystem 3.4: with the source one minute older the destination kept the old
            // contents, and with 'override' it takes the new ones.
            //
            // 'delete' is deliberately NOT set. It would remove destination files absent from the
            // source, which is the stale-file half of issue #1571 - but it also removes anything a
            // merchant or a module keeps inside its own directory, uploads included. That trade
            // needs the decision the issue is still open on.
            $filesystem->mirror(
                $updatedModulePath,
                $this->modulesFolder . DIRECTORY_SEPARATOR . $moduleUnzipperContext->getModuleName(),
                null,
                ['override' => true]
            );
        }
    }
}
