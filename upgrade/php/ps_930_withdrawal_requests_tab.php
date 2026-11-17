<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

use PrestaShop\Module\AutoUpgrade\Database\DbWrapper;

/**
 * Adds the "Sell > Customer Service > Withdrawal Requests" tab of the EU withdrawal function.
 */
function ps_930_withdrawal_requests_tab()
{
    include_once __DIR__ . '/add_new_tab.php';

    $tabId = add_new_tab_17(
        'AdminWithdrawalRequests',
        'en:Withdrawal Requests',
        0,
        true,
        'AdminParentCustomerThreads'
    );

    DbWrapper::execute(sprintf(
        'UPDATE `' . _DB_PREFIX_ . 'tab` SET `active` = 1, `enabled` = 1, `wording` = "%s", `wording_domain` = "%s", `route_name` = "%s" WHERE `id_tab` = %d',
        'Withdrawal Requests',
        'Admin.Navigation.Menu',
        'admin_withdrawal_requests_index',
        (int) $tabId
    ));
}
