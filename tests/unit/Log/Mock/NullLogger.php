<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

use PrestaShop\Module\AutoUpgrade\Log\Logger;

class NullLogger extends Logger
{
    public function __construct($fd)
    {
        $this->fd = $fd;
    }

    public function getLastInfo(): ?string
    {
        return null;
    }
}
