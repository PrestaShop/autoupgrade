<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Parameters\Validator;

use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;

abstract class AbstractConfigurationValidator
{
    /** @var Translator */
    protected $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param array<string, mixed> $array
     *
     * @return array<array{'message': string, 'target': string}>
     */
    abstract public function validate(array $array = []): array;

    /**
     * @param string|bool $boolValue
     */
    protected function validateBool($boolValue, string $key): ?string
    {
        if (!is_bool($boolValue) && ($boolValue === '' || filter_var($boolValue, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === null)) {
            return $this->translator->trans('Value must be a boolean for %s', [$key]);
        }

        return null;
    }

    /**
     * @param string|int $intValue
     */
    protected function validateInt($intValue, string $key): ?string
    {
        if (!is_int($intValue) && ($intValue === '' || filter_var($intValue, FILTER_VALIDATE_INT) === false)) {
            return $this->translator->trans('Value must be an integer for %s', [$key]);
        }

        return null;
    }
}
