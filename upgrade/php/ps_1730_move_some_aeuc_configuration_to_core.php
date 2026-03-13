<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
function ps_1730_move_some_aeuc_configuration_to_core()
{
    $translator = Context::getContext()->getTranslator();

    $labelInStock = [];
    $labelOOSProductsBOA = [];
    $labelOOSProductsBOD = [];
    $deliveryTimeAvailable = [];
    $deliveryTimeOutOfStockBackorderAllowed = [];

    foreach (Language::getLanguages() as $language) {
        $labelInStock[$language['id_lang']] = $translator->trans('In Stock', [], 'Admin.Shopparameters.Feature', $language['locale']);
        $labelOOSProductsBOA[$language['id_lang']] = $translator->trans('Product available for orders', [], 'Admin.Shopparameters.Feature', $language['locale']);
        $labelOOSProductsBOD[$language['id_lang']] = $translator->trans('Out-of-Stock', [], 'Admin.Shopparameters.Feature', $language['locale']);

        if ($value = Configuration::get('AEUC_LABEL_DELIVERY_TIME_AVAILABLE', $language['id_lang'])) {
            $deliveryTimeAvailable[$language['id_lang']] = $value;
        }

        if ($value = Configuration::get('AEUC_LABEL_DELIVERY_TIME_OOS', $language['id_lang'])) {
            $deliveryTimeOutOfStockBackorderAllowed[$language['id_lang']] = $value;
        }
    }

    Configuration::updateValue('PS_LABEL_IN_STOCK_PRODUCTS', $labelInStock);
    Configuration::updateValue('PS_LABEL_OOS_PRODUCTS_BOA', $labelOOSProductsBOA);
    Configuration::updateValue('PS_LABEL_OOS_PRODUCTS_BOD', $labelOOSProductsBOD);
    Configuration::updateValue('PS_LABEL_DELIVERY_TIME_AVAILABLE', $deliveryTimeAvailable);
    Configuration::updateValue('PS_LABEL_DELIVERY_TIME_OOSBOA', $deliveryTimeOutOfStockBackorderAllowed);
}
