<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

use PrestaShop\Module\AutoUpgrade\Database\DbWrapper;

/*
Example of PREFIX_image_type from a shop installed on PS 9.0.0
+---------------+-------------------+-------+--------+----------+------------+---------------+-----------+--------+-------------+
| id_image_type | name              | width | height | products | categories | manufacturers | suppliers | stores | theme_name  |
+---------------+-------------------+-------+--------+----------+------------+---------------+-----------+--------+-------------+
|             1 | cart_default      |   125 |    125 |        1 |          0 |             0 |         0 |      0 | classic     |
|             2 | small_default     |    98 |     98 |        1 |          1 |             1 |         1 |      0 | classic     |
|             3 | medium_default    |   452 |    452 |        1 |          0 |             1 |         1 |      0 | classic     |
|             4 | home_default      |   250 |    250 |        1 |          0 |             0 |         0 |      0 | classic     |
|             5 | large_default     |   800 |    800 |        1 |          0 |             1 |         1 |      0 | classic     |
|             6 | category_default  |   141 |    180 |        0 |          1 |             0 |         0 |      0 | classic     |
|             7 | stores_default    |   170 |    115 |        0 |          0 |             0 |         0 |      1 | classic     |
|             8 | cart_default      |   125 |    125 |        1 |          0 |             0 |         0 |      0 | hummingbird |
|             9 | small_default     |    98 |     98 |        1 |          1 |             1 |         1 |      0 | hummingbird |
|            10 | medium_default    |   452 |    452 |        1 |          0 |             1 |         1 |      0 | hummingbird |
|            11 | large_default     |   800 |    800 |        1 |          0 |             1 |         1 |      0 | hummingbird |
|            12 | home_default      |   250 |    250 |        1 |          0 |             0 |         0 |      0 | hummingbird |
|            13 | category_default  |   141 |    180 |        0 |          1 |             0 |         0 |      0 | hummingbird |
|            14 | stores_default    |   287 |    160 |        0 |          0 |             0 |         0 |      1 | hummingbird |
|            15 | default_xs        |   160 |    160 |        1 |          1 |             1 |         1 |      0 | hummingbird |
|            16 | default_sm        |   216 |    216 |        1 |          1 |             0 |         0 |      0 | hummingbird |
|            17 | default_md        |   261 |    261 |        1 |          1 |             1 |         1 |      0 | hummingbird |
|            18 | default_lg        |   336 |    336 |        1 |          1 |             0 |         0 |      0 | hummingbird |
|            19 | default_xl        |   400 |    400 |        1 |          0 |             0 |         0 |      0 | hummingbird |
|            20 | product_main      |   720 |    720 |        1 |          0 |             0 |         0 |      0 | hummingbird |
|            21 | category_cover    |  1000 |    200 |        0 |          1 |             0 |         0 |      0 | hummingbird |
|            22 | product_main_2x   |  1440 |   1440 |        1 |          0 |             0 |         0 |      0 | hummingbird |
|            23 | category_cover_2x |  2000 |    400 |        0 |          1 |             0 |         0 |      0 | hummingbird |
+---------------+-------------------+-------+--------+----------+------------+---------------+-----------+--------+-------------+
23 rows in set (0.001 sec)

Example of PREFIX_image_type from a shop updated to PS 9.0.0
+---------------+-------------------+-------+--------+----------+------------+---------------+-----------+--------+-------------+
| id_image_type | name              | width | height | products | categories | manufacturers | suppliers | stores | theme_name  |
+---------------+-------------------+-------+--------+----------+------------+---------------+-----------+--------+-------------+
|             1 | cart_default      |   125 |    125 |        1 |          0 |             0 |         0 |      0 | NULL        |
|             2 | small_default     |    98 |     98 |        1 |          1 |             1 |         1 |      0 | NULL        |
|             3 | medium_default    |   452 |    452 |        1 |          0 |             1 |         1 |      0 | NULL        |
|             4 | home_default      |   250 |    250 |        1 |          0 |             0 |         0 |      0 | NULL        |
|             5 | large_default     |   800 |    800 |        1 |          0 |             1 |         1 |      0 | NULL        |
|             6 | category_default  |   141 |    180 |        0 |          1 |             0 |         0 |      0 | NULL        |
|             7 | stores_default    |   170 |    115 |        0 |          0 |             0 |         0 |      1 | NULL        |
|             8 | cart_default      |   125 |    125 |        1 |          0 |             0 |         0 |      0 | hummingbird |
|             9 | small_default     |    98 |     98 |        1 |          1 |             1 |         1 |      0 | hummingbird |
|            10 | medium_default    |   452 |    452 |        1 |          0 |             1 |         1 |      0 | hummingbird |
|            11 | home_default      |   250 |    250 |        1 |          0 |             0 |         0 |      0 | hummingbird |
|            12 | large_default     |   800 |    800 |        1 |          0 |             1 |         1 |      0 | hummingbird |
|            13 | category_default  |   141 |    180 |        0 |          1 |             0 |         0 |      0 | hummingbird |
|            14 | stores_default    |   170 |    115 |        0 |          0 |             0 |         0 |      1 | hummingbird |
|            15 | default_xs        |   120 |    120 |        1 |          1 |             1 |         1 |      0 | hummingbird |
|            16 | default_s         |   160 |    160 |        1 |          1 |             0 |         0 |      0 | hummingbird |
|            17 | default_m         |   200 |    200 |        1 |          0 |             1 |         1 |      1 | hummingbird |
|            18 | default_md        |   320 |    320 |        1 |          1 |             0 |         0 |      0 | hummingbird |
|            19 | default_xl        |   400 |    400 |        1 |          0 |             0 |         0 |      1 | hummingbird |
|            20 | product_main      |   720 |    720 |        1 |          0 |             0 |         0 |      0 | hummingbird |
|            21 | category_cover    |  1000 |    200 |        0 |          1 |             0 |         0 |      0 | hummingbird |
|            22 | product_main_2x   |  1440 |   1440 |        1 |          0 |             0 |         0 |      0 | hummingbird |
|            23 | category_cover_2x |  2000 |    400 |        0 |          1 |             0 |         0 |      0 | hummingbird |
+---------------+-------------------+-------+--------+----------+------------+---------------+-----------+--------+-------------+
23 rows in set (0.001 sec)
*/

/**
 * @throws \PrestaShop\Module\AutoUpgrade\Exceptions\UpdateDatabaseException
 */
function ps_901_set_unicity_on_image_type(): bool
{
    // Request can't be nested because of this error:
    // ERROR 1267 (HY000): Illegal mix of collations (utf8mb4_general_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '='
    // Need to retrieve the list of themes in a dedicated request.
    $themesListResult = DbWrapper::executeS('SELECT `theme_name` FROM ' . _DB_PREFIX_ . 'shop GROUP BY `theme_name`');

    $themesList = array_column((array) $themesListResult, 'theme_name');

    // Keep most recent values of duplicated image type related to active themes.
    // This request covers the case where different shops on a multi-environment have different themes, where we will keep the highest ID.
    // Subrequest is nested 2 times to avoid Error 1093 "You can't specify target table 'image_type' for update in FROM clause"
    return DbWrapper::execute('DELETE FROM ' . _DB_PREFIX_ . 'image_type
        WHERE id_image_type NOT IN (
            SELECT `max_id_image_type` FROM (
                SELECT MAX(`id_image_type`) AS `max_id_image_type`
                FROM ' . _DB_PREFIX_ . 'image_type
                WHERE `theme_name` IS NULL OR `theme_name` IN ("' . implode('", "', $themesList) . '")
                GROUP BY `name`
            ) AS derived
        )
    ');
}
