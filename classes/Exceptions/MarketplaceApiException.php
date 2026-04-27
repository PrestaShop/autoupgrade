<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Exceptions;

use Exception;

class MarketplaceApiException extends Exception
{
    const API_NOT_CALLABLE_CODE = 0;
    const VERSION_NOT_FOUND_CODE = 1;
    const EMPTY_DATA_CODE = 2;
}
