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

namespace PrestaShop\Module\AutoUpgrade;

use Symfony\Component\HttpFoundation\JsonResponse;

class AjaxResponseBuilder
{
    /**
     * @param array{newRoute?:string, addScript?:string} $options
     */
    public static function hydrationResponse(string $parentToUpdate, string $newContent, ?array $options = []): JsonResponse
    {
        $arrayToReturn = [
            'kind' => 'hydrate',
            'hydration' => true,
            'parent_to_update' => $parentToUpdate,
            'new_content' => $newContent,
        ];

        if ($options['newRoute']) {
            $arrayToReturn['new_route'] = $options['newRoute'];
        }

        if ($options['addScript']) {
            $arrayToReturn['add_script'] = $options['addScript'];
        }

        return new JsonResponse($arrayToReturn);
    }

    public static function nextRouteResponse(string $nextRoute): JsonResponse
    {
        return new JsonResponse([
            'kind' => 'next',
            'next_route' => $nextRoute,
        ]);
    }

    public static function errorResponse(string $error, ?int $errorNumber = null): JsonResponse
    {
        return new JsonResponse([
            'error' => $error,
        ], $errorNumber ?? 400);
    }
}
