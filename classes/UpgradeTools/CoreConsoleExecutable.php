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

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools;

use PrestaShop\Module\AutoUpgrade\Exceptions\CommandLineException;
use RuntimeException;

class CoreConsoleExecutable
{
    /** @var string */
    private $binConsolePath;

    public function __construct(string $shopRootPath)
    {
        $this->binConsolePath = $shopRootPath . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'console';
    }

    /**
     * The shell may be very minimal on some hosts (e.g. dash on Debian/Ubuntu, or restricted shells in shared hosting).
     * That often means the $PATH is stripped down and php is not found. So we try to rely first on the console file when it is executable.
     *
     * @return array{returnCode: int, output: string[]}
     */
    public function callCommand(string $args)
    {
        $fullCommand = $this->getBaseCommand() . ' ' . $args . ' 2>&1';

        $output = [];
        $returnCode = 0;

        exec($fullCommand, $output, $returnCode);

        return [
            'returnCode' => $returnCode,
            'output' => $output,
        ];
    }

    /**
     * Find out what command can be called to reach the bin/console of PrestaShop
     *
     * @throws RuntimeException
     */
    private function getBaseCommand(): string
    {
        $candidates = [
            'php ' . $this->binConsolePath,
            $this->binConsolePath,
        ];

        $returnCode = 0;
        $output = [];

        foreach ($candidates as $candidate) {
            exec($candidate . ' 2>&1', $output, $returnCode);

            if (!$returnCode) {
                return $candidate;
            }
        }

        throw new CommandLineException("Could not find a valid way to call PrestaShop's bin/console. Check your environment PATH or add executable permission on the file.\n" . implode("\n", $output));
    }
}
