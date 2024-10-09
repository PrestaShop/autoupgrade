<?php

namespace PrestaShop\Module\AutoUpgrade;

use Symfony\Component\HttpFoundation\JsonResponse;

class AjaxResponseBuilder
{
    public static function hydrationResponse(string $parentToUpdate, string $newContent, ?string $newRoute = null): JsonResponse
    {
        $arrayToReturn = [
            'hydration' => true,
            'parent_to_update' => $parentToUpdate,
            'new_content' => $newContent,
        ];

        if ($newRoute) {
            $arrayToReturn['new_route'] = $newRoute;
        }

        return new JsonResponse($arrayToReturn);
    }

    public static function nextRouteResponse(string $nextRoute): JsonResponse
    {
        return new JsonResponse([
            'next_route' => $nextRoute,
        ]);
    }

    public static function errorResponse(string $error, ?int $errorNumber): JsonResponse
    {
        return new JsonResponse([
            'error' => $error,
        ], $errorNumber ?? 400);
    }
}
