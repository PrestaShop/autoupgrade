<?php
/**
 * 2007-2019 PrestaShop SA and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\Module\AutoUpgrade\Addons;

use PrestaShop\Module\AutoUpgrade\Tools14;
use Configuration;

/**
 * Class CurlClient is a simple Addons client that uses Curl to perform its request.
 */
class CurlClient implements ClientInterface
{
    /** @var bool */
    private $isAddonsUp = true;

    /**
     * {@inheritdoc}
     */
    public function request($type, array $params)
    {
        $requestContent = $this->performRequest($type, $params);

        return $this->parseRequest($requestContent);
    }

    /**
     * @param string $type
     * @param array $params
     *
     * @return string
     */
    private function performRequest($type, array $params)
    {
        if (!$this->isAddonsUp) {
            return '';
        }

        $postQueryData = [
            'version' => isset($params['version']) ? $params['version'] : _PS_VERSION_,
            'iso_code' => Tools14::strtolower(isset($params['iso_country']) ? $params['iso_country'] : 'all'),
            'format' => isset($params['format']) ? $params['format'] : 'xml',
        ];
        if (isset($params['source'])) {
            $postQueryData['source'] = $params['source'];
        }

        $postData = http_build_query($postQueryData);

        $endPoint = 'api.addons.prestashop.com';

        $protocols = ['https', 'http'];
        switch ($type) {
            case 'native':
                $postData .= '&method=listing&action=native';

                break;

            case 'service':
                $postData .= '&method=listing&action=service';

                break;

                break;
            case 'must-have':
                $postData .= '&method=listing&action=must-have';

                break;

            case 'module':
                $postData .= '&method=module&id_module=' . urlencode($params['id_module']);
                if (isset($params['username_addons'], $params['password_addons'])) {
                    $postData .= '&username=' . urlencode($params['username_addons']) . '&password=' . urlencode($params['password_addons']);
                }

                break;
            case 'hosted_module':
                $postData .= '&method=module&id_module=' . urlencode($params['id_module']) . '&username=' . urlencode($params['hosted_email'])
                    . '&password=' . urlencode($params['password_addons'])
                    . '&shop_url=' . urlencode(isset($params['shop_url']) ? $params['shop_url'] : Tools14::getShopDomain())
                    . '&mail=' . urlencode(isset($params['email']) ? $params['email'] : Configuration::get('PS_SHOP_EMAIL'));

                break;
            case 'install-modules':
                $postData .= '&method=listing&action=install-modules';
                $postData .= defined('_PS_HOST_MODE_') ? '-od' : '';

                break;
            default:
                return '';
        }

        $context = stream_context_create(array(
            'http' => array(
                'method' => 'POST',
                'content' => $postData,
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'timeout' => 5,
            ),
        ));

        foreach ($protocols as $protocol) {
            if ($content = Tools14::file_get_contents($protocol . '://' . $endPoint, false, $context)) {
                return $content;
            }
        }

        $this->isAddonsUp = false;

        return '';
    }

    /**
     * @param string $requestContent
     *
     * @return array|bool
     */
    private function parseRequest($requestContent)
    {
        if (empty($requestContent)) {
            false;
        }

        $modules = @simplexml_load_string($requestContent);
        if (!$modules || !$modules->module->count()) {
            return false;
        }

        $addonsModules = [];
        foreach ($modules->module as $module) {
            $addonsModules[(int) $module->id] = (string) $module->name;
        }

        return $addonsModules;
    }
}
