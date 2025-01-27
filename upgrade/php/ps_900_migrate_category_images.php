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

/**
 * @return void
 *
 * @throws \PrestaShop\Module\AutoUpgrade\Exceptions\UpdateDatabaseException
 */
function ps_900_migrate_category_images()
{
    /*
     * Pre v9 worked like this:
     * There could be 123.jpg, a cover image.
     * There could be 123_thumb.jpg, a category miniature.
     *
     * If 123_thumb.jpg existed, it took it and generated category thumbnails from it.
     * But, overwriting thumbnails of 123.jpg.
     *
     * So you suddenly got one thumbnail that was from a different image.
     * Even worse, frontoffice and backoffice generated it as a different thumbnail,
     * one as small, one as medium.
     */
    $formattedNameSmall = ImageType::getFormattedName('small');
    $formattedNameMedium = ImageType::getFormattedName('medium');

    // Get categories on shop
    $categoryIds = DbWrapper::executeS('SELECT id_category FROM `' . _DB_PREFIX_ . 'category`');
    if (empty($categoryIds)) {
        return;
    }

    foreach ($categoryIds as $row) {
        // Get the ID
        $categoryId = (int) $row['id_category'];

        /*
         * Define paths to original files.
         * Define paths to possible broken thumbnails.
         */
        $categoryCoverPath = _PS_CAT_IMG_DIR_ . $categoryId . '.jpg';
        $categoryMiniaturePath = _PS_CAT_IMG_DIR_ . $categoryId . '_thumb.jpg';
        $categorySmallPath = _PS_CAT_IMG_DIR_ . $categoryId . '-' . $formattedNameSmall;
        $categoryMediumPath = _PS_CAT_IMG_DIR_ . $categoryId . '-' . $formattedNameMedium;

        // Check what images exist
        $hasCover = file_exists($categoryCoverPath);
        $hasMiniature = file_exists($categoryMiniaturePath);

        /*
         * If both exist, generated thumbnails can be wrong.
         * We delete them and we are finished.
         */
        if ($hasCover && $hasMiniature) {
            deleteAllThumbnailsIfExist($categorySmallPath);
            deleteAllThumbnailsIfExist($categoryMediumPath);
            continue;
        }

        /*
         * If it has a cover but no miniature, we don't want people
         * to end up with no miniature. We will copy the cover
         * as the miniature. If the destination file already exists,
         * it will be overwritten.
         */
        if ($hasCover && !$hasMiniature) {
            @copy($categoryCoverPath, $categoryMiniaturePath);
            continue;
        }

        /*
         * If it has a miniature but no cover, we will delete thumbnails,
         * because they have a wrong name and we don't want them to make
         * issues.
         */
        if (!$hasCover && $hasMiniature) {
            deleteAllThumbnailsIfExist($categorySmallPath);
            deleteAllThumbnailsIfExist($categoryMediumPath);
        }
    }
}

function deleteAllThumbnailsIfExist($pathWithNoExtension)
{
    foreach (['jpg', 'png', 'webp', 'avif'] as $extension) {
        $path = $pathWithNoExtension . '.' . $extension;
        if (file_exists($path)) {
            @unlink($path);
        }
    }
}
