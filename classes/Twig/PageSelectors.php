<?php

namespace PrestaShop\Module\AutoUpgrade\Twig;

class PageSelectors
{
    public const PAGE_PARENT_ID = 'update_assistant';
    public const STEP_PARENT_ID = 'ua_container';

    /**
     * @return array<string, string>
     */
    public static function getAllSelectors(): array
    {
        return [
            'page_parent_id' => self::PAGE_PARENT_ID,
            'step_parent_id' => self::STEP_PARENT_ID,
        ];
    }
}
