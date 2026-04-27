<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Tests\Twig;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Task\TaskType;
use PrestaShop\Module\AutoUpgrade\Twig\Steps\Stepper;
use PrestaShop\Module\AutoUpgrade\Twig\Steps\UpdateSteps;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;

class StepperTest extends TestCase
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

        $this->updateSteps = new Stepper($this->translator, TaskType::TASK_TYPE_UPDATE);
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
