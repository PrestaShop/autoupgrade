<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Log;

/**
 * This class reimplement the old properties of the class AdminSelfUpgrade,
 * where all the mesages were stored.
 */
class WebLogger extends Logger
{
    /** @var string[] */
    protected $normalMessages = [];

    /** @var ?string */
    protected $lastInfo;

    /**
     * {@inheritdoc}
     *
     * @return string[]
     */
    public function getLogs(): array
    {
        return $this->normalMessages;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastInfo(): ?string
    {
        return $this->lastInfo;
    }

    private function formatLog(int $level, string $message): string
    {
        return self::$levels[$level] . ' - ' . $message;
    }

    /**
     * {@inheritdoc}
     *
     * @param array<mixed> $context
     */
    public function log($level, string $message, array $context = []): void
    {
        if (empty($message)) {
            return;
        }

        $message = $this->cleanFromSensitiveData($message);
        parent::log($level, $message, $context);

        if ($level === self::INFO) {
            $this->lastInfo = $message;
        }

        $log = $this->formatLog($level, $message);

        $this->normalMessages[] = $log;
    }
}
