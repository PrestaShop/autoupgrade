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

namespace PrestaShop\Module\AutoUpgrade\Module;

use PrestaShop\Module\AutoUpgrade\Tools14;

/**
 * Class AddonsCurlClient is a simple Addons client that uses Curl to perform its request.
 */
class AddonsCurlClient implements AddonsClientInterface
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

        $post_query_data = array(
            'version' => isset($params['version']) ? $params['version'] : _PS_VERSION_,
            'iso_code' => Tools14::strtolower(isset($params['iso_country']) ? $params['iso_country'] : 'all'),
            'format' => isset($params['format']) ? $params['format'] : 'xml',
        );
        if (isset($params['source'])) {
            $post_query_data['source'] = $params['source'];
        }

        $post_data = http_build_query($post_query_data);

        $end_point = 'api.addons.prestashop.com';

        switch ($type) {
            case 'native':
                $post_data .= '&method=listing&action=native';

                break;

            case 'service':
                $post_data .= '&method=listing&action=service';

                break;

                break;
            case 'must-have':
                $post_data .= '&method=listing&action=must-have';

                break;

            case 'module':
                $post_data .= '&method=module&id_module=' . urlencode($params['id_module']);
                if (isset($params['username_addons'], $params['password_addons'])) {
                    $post_data .= '&username=' . urlencode($params['username_addons']) . '&password=' . urlencode($params['password_addons']);
                }

                break;
            case 'hosted_module':
                $post_data .= '&method=module&id_module=' . urlencode((int) $params['id_module']) . '&username=' . urlencode($params['hosted_email'])
                    . '&password=' . urlencode($params['password_addons'])
                    . '&shop_url=' . urlencode(isset($params['shop_url']) ? $params['shop_url'] : Tools14::getShopDomain())
                    . '&mail=' . urlencode(isset($params['email']) ? $params['email'] : Configuration::get('PS_SHOP_EMAIL'));

                break;
            case 'install-modules':
                $post_data .= '&method=listing&action=install-modules';
                $post_data .= defined('_PS_HOST_MODE_') ? '-od' : '';

                break;
            default:
                return '';
        }

        $context = stream_context_create(array(
            'http' => array(
                'method' => 'POST',
                'content' => $post_data,
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'timeout' => 5,
            ),
        ));

        if ($content = Tools14::file_get_contents('https://' . $end_point, false, $context)) {
            return $content;
        }

        if ($content = Tools14::file_get_contents('http://' . $end_point, false, $context)) {
            return $content;
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
        if (!$modules || !count($modules->module)) {
            return false;
        }

        $addonsModules = [];
        foreach ($modules->module as $module) {
            $addonsModules[(int) $module->id] = (string) $module->name;
        }

        return $addonsModules;
    }
}
