<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
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

        if (!empty($options['newRoute'])) {
            $arrayToReturn['new_route'] = $options['newRoute'];
        }

        if (!empty($options['addScript'])) {
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
