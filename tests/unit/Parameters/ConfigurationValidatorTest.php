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

namespace Parameters;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Parameters\ConfigurationValidator;
use UnexpectedValueException;

class ConfigurationValidatorTest extends TestCase
{
    public function testValidateChannelSuccess()
    {
        $validator = new ConfigurationValidator();

        $validator->validate(['channel' => 'online']);
        $validator->validate(['channel' => 'local']);
    }

    public function testValidateChannelFail()
    {
        $validator = new ConfigurationValidator();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Unknown channel toto');

        $validator->validate(['channel' => 'toto']);
    }

    public function testValidateZipSuccess()
    {
        $validator = new ConfigurationValidator();

        $validator->validate(['archive_zip' => 'toto']);
        $validator->validate(['channel' => 'local', 'archive_zip' => 'toto']);
        $validator->validate(['channel' => 'online', 'archive_zip' => '']);
    }

    public function testValidateZipFail()
    {
        $validator = new ConfigurationValidator();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('No zip archive provided');

        $validator->validate(['channel' => 'local', 'archive_zip' => '']);
    }

    public function testValidateXmlSuccess()
    {
        $validator = new ConfigurationValidator();

        $validator->validate(['archive_xml' => 'toto']);
        $validator->validate(['channel' => 'local', 'archive_xml' => 'toto']);
        $validator->validate(['channel' => 'online', 'archive_xml' => '']);
    }

    public function testValidateXmlFail()
    {
        $validator = new ConfigurationValidator();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('No xml archive provided');

        $validator->validate(['channel' => 'local', 'archive_xml' => '']);
    }

    public function testValidateBoolSuccess()
    {
        $validator = new ConfigurationValidator();

        $validator->validate(['PS_AUTOUP_CUSTOM_MOD_DESACT' => '1']);
        $validator->validate(['PS_AUTOUP_CUSTOM_MOD_DESACT' => '0']);
        $validator->validate(['PS_AUTOUP_CUSTOM_MOD_DESACT' => 'true']);
        $validator->validate(['PS_AUTOUP_CUSTOM_MOD_DESACT' => 'false']);
        $validator->validate(['PS_AUTOUP_CUSTOM_MOD_DESACT' => 'on']);
        $validator->validate(['PS_AUTOUP_CUSTOM_MOD_DESACT' => 'off']);
    }

    public function testValidateBoolFail()
    {
        $validator = new ConfigurationValidator();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Value must be a boolean for PS_AUTOUP_CUSTOM_MOD_DESACT');

        $validator->validate(['PS_AUTOUP_CUSTOM_MOD_DESACT' => 'toto']);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Value must be a boolean for PS_AUTOUP_CUSTOM_MOD_DESACT');

        $validator->validate(['PS_AUTOUP_CUSTOM_MOD_DESACT' => '']);
    }
}
