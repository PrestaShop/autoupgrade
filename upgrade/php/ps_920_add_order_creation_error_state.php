<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * Creates the "Order creation error" order status and stores its id in the
 * PS_OS_CREATION_ERROR configuration key on shops upgrading from a version
 * that did not ship this status.
 *
 * Orders whose creation aborted on a server error are saved with no
 * current_state (0). Without a dedicated status the back office renders such
 * orders with the first status in the list pre-selected, which is misleading.
 * This status lets merchants spot and fix them. See PrestaShop issue #32743.
 *
 * This script is invoked from upgrade/sql/9.2.0.sql via the line:
 *     /* PHP:ps_920_add_order_creation_error_state(); *\/;
 * The autoupgrade runner require_once's upgrade/php/<lowercased function
 * name>.php, so the file name and the function name below MUST match. The
 * version it runs at is decided by the SQL file that references it, not by the
 * ps_920_ prefix. If the fix lands in a later release, move the /* PHP: *\/ line
 * to that release's SQL file and rename file + function accordingly.
 *
 * @return bool
 */
function ps_920_add_order_creation_error_state()
{
    // Idempotent: skip if a previous run (or a catch-up script) already created it.
    if ((int) Configuration::get('PS_OS_CREATION_ERROR') > 0) {
        return true;
    }

    $orderState = new OrderState();
    $orderState->send_email = false;
    $orderState->invoice = false;
    $orderState->color = '#E74C3C';
    $orderState->unremovable = true;
    $orderState->logable = false;
    $orderState->delivery = false;
    $orderState->shipped = false;
    $orderState->paid = false;
    $orderState->hidden = false;
    $orderState->pdf_invoice = false;
    $orderState->pdf_delivery = false;

    // One localized name per installed language, taken from the same source
    // string registered in classes/lang/KeysReference/OrderStateLang.php.
    $translator = Context::getContext()->getTranslator();
    $orderState->name = array();
    $orderState->template = array();
    foreach (Language::getLanguages(false) as $lang) {
        $orderState->name[$lang['id_lang']] = $translator->trans(
            'Order creation error',
            array(),
            'Admin.Orderscustomers.Feature',
            $lang['locale']
        );
        // No customer email is sent for this status.
        $orderState->template[$lang['id_lang']] = '';
    }

    if (!$orderState->add()) {
        return false;
    }

    // Persist the mapping so the core can reference the new status.
    Configuration::updateGlobalValue('PS_OS_CREATION_ERROR', (int) $orderState->id);

    // Give it a status icon (img/os/<id>.gif). Reuse the payment-error icon as
    // a safe default; replace later with a dedicated visual if desired.
    $source = _PS_ROOT_DIR_ . '/img/os/' . (int) Configuration::get('PS_OS_ERROR') . '.gif';
    $target = _PS_ROOT_DIR_ . '/img/os/' . (int) $orderState->id . '.gif';
    if (file_exists($source) && !file_exists($target)) {
        @copy($source, $target);
    }

    return true;
}
