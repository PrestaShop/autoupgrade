<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

use PrestaShop\Module\AutoUpgrade\Database\DbWrapper;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use PrestaShop\PrestaShop\Core\Localization\CLDR\LocaleRepository;

/**
 * On PrestaShop 1.7.6.0, two new columns have been introduced in the PREFIX_currency table: precision & numeric_iso_code
 * A fresh install would add the proper data in these columns, however an upgraded shop to 1.7.6.0 would not get the
 * corresponding values of each currency.
 *
 * This upgrade script will cover this need by loading the CLDR data and update the currency if it still has the default table values.
 *
 * @throws \PrestaShop\Module\AutoUpgrade\Exceptions\UpdateDatabaseException
 */
function ps_1761_update_currencies()
{
    // Force cache reset of languages (load locale column)
    ObjectModel::disableCache();

    /** @var Currency[] $currencies */
    $currencies = Currency::getCurrencies(true, false);
    $context = Context::getContext();
    $container = isset($context->controller) ? $context->controller->getContainer() : null;
    if (null === $container) {
        $container = SymfonyContainer::getInstance();
    }

    /** @var LocaleRepository $localeRepoCLDR */
    $localeRepoCLDR = $container->get('prestashop.core.localization.cldr.locale_repository');
    // CLDR locale give us the CLDR reference specification
    $cldrLocale = $localeRepoCLDR->getLocale($context->language->getLocale());

    foreach ($currencies as $currency) {
        if ((int) $currency->precision !== 6 || !empty((int) $currency->numeric_iso_code)) {
            continue;
        }
        // CLDR currency gives data from CLDR reference, for the given language
        $cldrCurrency = $cldrLocale->getCurrency($currency->iso_code);
        if (!empty($cldrCurrency)) {
            $currency->precision = (int) $cldrCurrency->getDecimalDigits();
            $currency->numeric_iso_code = $cldrCurrency->getNumericIsoCode();
        }
        DbWrapper::execute(
            'UPDATE `' . _DB_PREFIX_ . 'currency`
            SET `precision` = ' . $currency->precision . ', `numeric_iso_code` = ' . $currency->numeric_iso_code . '
            WHERE `id_currency` = ' . $currency->id
        );
    }

    ObjectModel::enableCache();
}
