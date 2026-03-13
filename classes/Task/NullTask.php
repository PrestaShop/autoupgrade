<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Task;

class NullTask extends AbstractTask
{
    public function run(): int
    {
        return ExitCode::SUCCESS;
    }
}
