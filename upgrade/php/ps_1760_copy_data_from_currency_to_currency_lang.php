<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

use PrestaShop\Module\AutoUpgrade\Database\DbWrapper;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use PrestaShop\PrestaShop\Core\Localization\CLDR\LocaleRepository;

/**
 * @return void
 *
 * @throws \PrestaShop\Module\AutoUpgrade\Exceptions\UpdateDatabaseException
 */
function ps_1760_copy_data_from_currency_to_currency_lang()
{
    // Force cache reset of languages (load locale column)
    ObjectModel::disableCache();

    $languages = Language::getLanguages();
    foreach ($languages as $language) {
        DbWrapper::execute(
            'INSERT INTO `' . _DB_PREFIX_ . 'currency_lang` (`id_currency`, `id_lang`, `name`)
            SELECT `id_currency`, ' . (int) $language['id_lang'] . ' as id_lang , `name`
            FROM `' . _DB_PREFIX_ . 'currency`
            ON DUPLICATE KEY UPDATE
            `name` = `' . _DB_PREFIX_ . 'currency`.`name`
            '
        );
    }
    /** @var Currency[] $currencies */
    $currencies = Currency::getCurrencies(true, false);
    $context = Context::getContext();
    $container = isset($context->controller) ? $context->controller->getContainer() : null;
    if (null === $container) {
        $container = SymfonyContainer::getInstance();
    }

    /** @var LocaleRepository $localeRepoCLDR */
    $localeRepoCLDR = $container->get('prestashop.core.localization.cldr.locale_repository');
    foreach ($currencies as $currency) {
        refreshLocalizedCurrencyData($currency, $languages, $localeRepoCLDR);
    }

    ObjectModel::enableCache();
}

/**
 * @throws \PrestaShop\Module\AutoUpgrade\Exceptions\UpdateDatabaseException
 */
function refreshLocalizedCurrencyData(Currency $currency, array $languages, LocaleRepository $localeRepoCLDR)
{
    $language = new Language($languages[0]['id_lang']);
    $cldrLocale = $localeRepoCLDR->getLocale($language->locale);
    $cldrCurrency = $cldrLocale->getCurrency($currency->iso_code);

    if (!empty($cldrCurrency)) {
        $fields = [
            'numeric_iso_code' => $cldrCurrency->getNumericIsoCode(),
            'precision' => $cldrCurrency->getDecimalDigits(),
        ];
        DbWrapper::update('currency', $fields, 'id_currency = ' . (int) $currency->id);
    }

    foreach ($languages as $languageData) {
        $language = new Language($languageData['id_lang']);
        if (empty($language->locale)) {
            // Language doesn't have locale we can't install this language
            continue;
        }

        // CLDR locale give us the CLDR reference specification
        $cldrLocale = $localeRepoCLDR->getLocale($language->locale);
        // CLDR currency gives data from CLDR reference, for the given language
        $cldrCurrency = $cldrLocale->getCurrency($currency->iso_code);

        if (empty($cldrCurrency)) {
            continue;
        }

        $fields = [
            'name' => $cldrCurrency->getDisplayName(),
            'symbol' => (string) $cldrCurrency->getSymbol() ?: $currency->iso_code,
        ];

        $where = 'id_currency = ' . (int) $currency->id
            . ' AND id_lang = ' . (int) $language->id;
        DbWrapper::update('currency_lang', $fields, $where);
    }
}
