<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace Composer\Script {
    class Event
    {
    }
}

namespace PrestaShop\Module\AutoUpgrade {
    use Composer\Script\Event;
    use PrestaShop\Module\AutoUpgrade\Log\LoggerInterface;

    class LoggedEvent extends Event
    {
        /**
         * @var LoggerInterface
         */
        private $logger;

        public function __construct(LoggerInterface $logger)
        {
            $this->logger = $logger;
        }

        public function getIO(): LoggedEventIo
        {
            return new LoggedEventIo($this->logger);
        }
    }
}
