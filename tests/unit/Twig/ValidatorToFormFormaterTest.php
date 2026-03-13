<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Tests\Twig;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Twig\ValidatorToFormFormater;

class ValidatorToFormFormaterTest extends TestCase
{
    public function testEmptyArray()
    {
        $input = [];
        $expected = [];

        $actual = ValidatorToFormFormater::format($input);

        $this->assertSame($expected, $actual);
    }

    public function testErrorsForSpecificFields()
    {
        $input = [
            [
                'message' => 'Oh no',
                'target' => 'oneDisgustingField',
            ],
        ];
        $expected = [
            'oneDisgustingField' => 'Oh no',
        ];

        $actual = ValidatorToFormFormater::format($input);

        $this->assertSame($expected, $actual);
    }

    public function testGlobalErrors()
    {
        $input = [
            [
                'message' => 'Eww',
            ],
        ];
        $expected = [
            'global' => 'Eww',
        ];

        $actual = ValidatorToFormFormater::format($input);

        $this->assertSame($expected, $actual);
    }

    public function testMixedErrors()
    {
        $input = [
            [
                'message' => 'This field cannot be blank',
                'target' => 'theMandatoryField',
            ],
            [
                'message' => 'Eww',
            ],
            [
                'message' => 'Oh no',
                'target' => 'oneDisgustingField',
            ],
        ];
        $expected = [
            'theMandatoryField' => 'This field cannot be blank',
            'global' => 'Eww',
            'oneDisgustingField' => 'Oh no',
        ];

        $actual = ValidatorToFormFormater::format($input);

        $this->assertSame($expected, $actual);
    }
}
