<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

use PrestaShop\Module\AutoUpgrade\Database\DbWrapper;

/**
 * Initialize feature value positions after the position column is added in 9.0.0.
 *
 * Existing shops upgraded to 9 may already contain feature values, which all get
 * the default position 0 when the column is introduced. We backfill sequential
 * positions per feature to avoid duplicates and preserve a stable ordering.
 *
 * @return void
 *
 * @throws \PrestaShop\Module\AutoUpgrade\Exceptions\UpdateDatabaseException
 */
function ps_900_init_feature_value_positions()
{
    $hasInitializedPositions = (int) DbWrapper::getValue('
        SELECT COUNT(*)
        FROM `' . _DB_PREFIX_ . 'feature_value`
        WHERE `position` <> 0
    ');

    if ($hasInitializedPositions > 0) {
        return;
    }

    $featureValues = DbWrapper::executeS('
        SELECT `id_feature_value`, `id_feature`
        FROM `' . _DB_PREFIX_ . 'feature_value`
        ORDER BY `id_feature` ASC, `id_feature_value` ASC
    ');

    if (empty($featureValues)) {
        return;
    }

    $currentFeatureId = null;
    $position = -1;

    foreach ($featureValues as $featureValue) {
        $featureId = (int) $featureValue['id_feature'];

        if ($featureId !== $currentFeatureId) {
            $currentFeatureId = $featureId;
            $position = 0;
        } else {
            ++$position;
        }

        DbWrapper::update(
            'feature_value',
            ['position' => $position],
            '`id_feature_value` = ' . (int) $featureValue['id_feature_value']
        );
    }
}
