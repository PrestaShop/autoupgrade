<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Twig\Steps;

interface StepsInterface
{
    /**
     * @return array<self::STEP_*, array<string,string>>
     */
    public function getSteps(): array;
}
