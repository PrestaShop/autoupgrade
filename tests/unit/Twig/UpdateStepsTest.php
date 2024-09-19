<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace PrestaShop\Module\AutoUpgrade\Tests\Twig;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Twig\UpdateSteps;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;

class UpdateStepsTest extends TestCase
{
    /** @var Translator */
    private $translator;

    /** @var UpdateSteps */
    private $updateSteps;

    protected function setUp()
    {
        $this->translator = $this->createMock(Translator::class);

        $this->translator->method('trans')->willReturnCallback(function ($string) {
            return $string;
        });

        $this->updateSteps = new UpdateSteps($this->translator);
    }

    public function testGetSteps()
    {
        $steps = $this->updateSteps->getSteps(UpdateSteps::STEP_BACKUP);

        $this->assertCount(5, $steps);

        $this->assertEquals('done', $steps[0]['state']); // STEP_VERSION_CHOICE
        $this->assertEquals('done', $steps[1]['state']); // STEP_UPDATE_OPTIONS
        $this->assertEquals('current', $steps[2]['state']); // STEP_BACKUP
        $this->assertEquals('normal', $steps[3]['state']); // STEP_UPDATE
        $this->assertEquals('normal', $steps[4]['state']); // STEP_POST_UPDATE
    }

    public function testGetStepTitle()
    {
        $title = $this->updateSteps->getStepTitle(UpdateSteps::STEP_UPDATE);
        $this->assertEquals('Update', $title);
    }

    public function testGetStepParams()
    {
        $stepParams = $this->updateSteps->getStepParams(UpdateSteps::STEP_UPDATE_OPTIONS);

        // Assert the 'step' section of the returned array
        $this->assertArrayHasKey('step', $stepParams);
        $this->assertEquals(UpdateSteps::STEP_UPDATE_OPTIONS, $stepParams['step']['code']);
        $this->assertEquals('Update options', $stepParams['step']['title']);

        // Assert the 'steps' section of the returned array
        $this->assertArrayHasKey('steps', $stepParams);
        $this->assertCount(5, $stepParams['steps']);
    }
}
