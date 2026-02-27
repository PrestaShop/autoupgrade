<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
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
