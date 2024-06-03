<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace PrestaShop\Module\AutoUpgrade;

class Analytics
{
    const SEGMENT_CLIENT_KEY_PHP = 'NrWZk42rDrA56DkEt9Tj18DBirLoRLhj';
    const SEGMENT_CLIENT_KEY_JS = 'RM87m03McDSL4Fvm3GJ3piBPbAL3Fa2i';

    /**
     * @var string
     */
    private $anonymousId;

    /**
     * @var array
     */
    private $properties;

    /**
     * @param string $anonymousUserId
     * @param array{'properties'?: array} $options
     */
    public function __construct($anonymousUserId, array $options)
    {
        $this->anonymousId = hash('sha256', $anonymousUserId, false);
        $this->properties = $options['properties'] ?? [];

        \Segment::init(self::SEGMENT_CLIENT_KEY_PHP);
    }

    /**
     * @param string $event
     *
     * @return void
     */
    public function track($event)
    {
        \Segment::track(array_merge(
            ['event' => $event],
            $this->getProperties()
        ));
        \Segment::flush();
    }

    /**
     * @return array
     */
    protected function getProperties()
    {
        return [
            'anonymousId' => $this->anonymousId,
            'channel' => 'browser',
            'properties' => array_merge(
                $this->properties,
                [
                    'module' => 'autoupgrade',
                ]
            ),
        ];
    }
}
