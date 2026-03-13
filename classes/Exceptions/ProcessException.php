<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Exceptions;

use Exception;

class ProcessException extends Exception
{
    const SEVERITY_ERROR = 1;
    const SEVERITY_WARNING = 2;

    /**
     * @var string[]
     */
    private $quickInfos = [];

    /**
     * @var int
     */
    private $severity = self::SEVERITY_ERROR;

    /**
     * @return string[]
     */
    public function getQuickInfos(): array
    {
        if ($this->getPrevious()) {
            return array_merge(
                [(string) $this->getPrevious()],
                $this->quickInfos
            );
        }

        return $this->quickInfos;
    }

    public function getSeverity(): int
    {
        return $this->severity;
    }

    public function addQuickInfo(string $quickInfo): ProcessException
    {
        $this->quickInfos[] = $quickInfo;

        return $this;
    }

    /**
     * @param string[] $quickInfos
     *
     * @return $this
     */
    public function setQuickInfos(array $quickInfos): ProcessException
    {
        $this->quickInfos = $quickInfos;

        return $this;
    }

    public function setSeverity(int $severity): ProcessException
    {
        $this->severity = $severity;

        return $this;
    }
}
