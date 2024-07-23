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

namespace PrestaShop\Module\AutoUpgrade\Twig\Form;

use Configuration;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfiguration;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;
use Twig\Environment;

class FormRenderer
{
    /**
     * @var UpgradeConfiguration
     */
    private $config;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @param Environment $twig
     */
    public function __construct(
        UpgradeConfiguration $configuration,
        $twig,
        Translator $translator
    ) {
        $this->config = $configuration;
        $this->twig = $twig;
        $this->translator = $translator;
    }

    /**
     * @param array<string, array<string, string|array<string>>> $fields
     */
    public function render(string $name, array $fields, string $tabname): string
    {
        $formFields = [];

        foreach ($fields as $key => $field) {
            $html = '';
            $required = !empty($field['required']);
            $disabled = !empty($field['disabled']);

            if (in_array($key, UpgradeContainer::DB_CONFIG_KEYS)) {
                // values fetched from configuration in database
                $val = Configuration::get($key);
            } else {
                // other conf values are fetched from config file
                $val = $this->config->get($key);
            }

            if ($val === null) {
                $val = isset($field['defaultValue']) ? $field['defaultValue'] : false;
            }

            if (!in_array($field['type'], ['image', 'radio', 'select', 'container', 'bool', 'container_end']) || isset($field['show'])) {
                $html .= '<div style="clear: both; padding-top:15px">'
                    . ($field['title'] ? '<label >' . $field['title'] . '</label>' : '')
                    . '<div class="margin-form" style="padding-top:5px">';
            }

            // Display the appropriate input type for each field
            switch ($field['type']) {
                case 'disabled':
                    $html .= $field['disabled'];
                    break;

                case 'bool':
                    $html .= $this->renderBool($field, $key, $val);
                    break;

                case 'radio':
                    $html .= $this->renderRadio($field, $key, $val, $disabled);
                    break;

                case 'select':
                    $html .= $this->renderSelect($field, $key, $val);
                    break;

                case 'textarea':
                    $html .= $this->renderTextarea($field, $key, $val, $disabled);
                    break;

                case 'container':
                    $html .= '<div id="' . $key . '">';
                    break;

                case 'container_end':
                    $html .= (isset($field['content']) ? $field['content'] : '') . '</div>';
                    break;

                case 'text':
                default:
                    $html .= $this->renderTextField($field, $key, $val, $disabled);
            }

            if ($required && !in_array($field['type'], ['image', 'radio'])) {
                $html .= ' <sup>*</sup>';
            }

            if (isset($field['desc']) && !in_array($field['type'], ['bool', 'select'])) {
                $html .= '<p style="clear:both">';
                if (!empty($field['thumb']) && $field['thumb']['pos'] == 'after') {
                    $html .= $this->renderThumb($field);
                }
                $html .= $field['desc'] . '</p>';
            }

            if (!in_array($field['type'], ['image', 'radio', 'select', 'container', 'bool', 'container_end']) || isset($field['show'])) {
                $html .= '</div></div>';
            }

            $formFields[] = $html;
        }

        return $this->twig->render(
            '@ModuleAutoUpgrade/form.html.twig',
            [
                'name' => $name,
                'tabName' => $tabname,
                'fields' => $formFields,
            ]
        );
    }

    /**
     * @param array<string, string|array<string>> $field
     */
    private function renderBool(array $field, string $key, bool $val): string
    {
        return '<div class="form-group">
                <label class="col-lg-3 control-label">' . $field['title'] . '</label>
                    <div class="col-lg-9">
                        <span class="switch prestashop-switch fixed-width-lg">
                            <input type="radio" name="' . $key . '" id="' . $key . '_on" value="1" ' . ($val ? ' checked="checked"' : '') . (isset($field['js']['on']) ? $field['js']['on'] : '') . ' />
                            <label for="' . $key . '_on" class="radioCheck">
                                <i class="color_success"></i> '
                            . $this->translator->trans('Yes') . '
                            </label>
                            <input type="radio" name="' . $key . '" id="' . $key . '_off" value="0" ' . (!$val ? 'checked="checked"' : '') . (isset($field['js']['off']) ? $field['js']['off'] : '') . '/>
                            <label for="' . $key . '_off" class="radioCheck">
                                <i class="color_danger"></i> ' . $this->translator->trans('No') . '
                            </label>
                            <a class="slide-button btn"></a>
                        </span>
                        <div class="help-block">' . $field['desc'] . '</div>
                    </div>
                </div>';
    }

    /**
     * @param array<string, string|array<string>> $field
     */
    private function renderRadio(array $field, string $key, string $val, bool $disabled): string
    {
        $html = '';
        foreach ($field['choices'] as $cValue => $cKey) {
            $html .= '<input ' . ($disabled ? 'disabled="disabled"' : '') . ' type="radio" name="' . $key . '" id="' . $key . $cValue . '_on" value="' . (int) ($cValue) . '"' . (($cValue == $val) ? ' checked="checked"' : '') . (isset($field['js'][$cValue]) ? ' ' . $field['js'][$cValue] : '') . ' /><label class="t" for="' . $key . $cValue . '_on"> ' . $cKey . '</label><br />';
        }
        $html .= '<br />';

        return $html;
    }

    /**
     * @param array<string, string|array<string>> $field
     */
    private function renderSelect(array $field, string $key, string $val): string
    {
        $html = '<div class="form-group">
                    <label class="col-lg-3 control-label">' . $field['title'] . '</label>
                        <div class="col-lg-9">
                            <select name="' . $key . '">';

        foreach ($field['choices'] as $cValue => $cKey) {
            $html .= '<option value="' . (int) $cValue . '"'
                . (($cValue == $val) ? ' selected' : '')
                . '>'
                . $cKey
                . '</option>';
        }

        $html .= '</select>
                <div class="help-block">' . $field['desc'] . '</div>
            </div>
        </div>';

        return $html;
    }

    /**
     * @param array<string, string|array<string>> $field
     */
    private function renderTextarea(array $field, string $key, string $val, bool $disabled): string
    {
        return '<textarea '
            . ($disabled ? 'disabled="disabled"' : '')
            . ' name="' . $key
            . '" cols="' . $field['cols']
            . '" rows="' . $field['rows']
            . '">'
            . htmlentities($val, ENT_COMPAT, 'UTF-8')
            . '</textarea>';
    }

    /**
     * @param array<string, string|array<string>> $field
     */
    private function renderTextField(array $field, string $key, string $val, bool $disabled): string
    {
        return '<input '
            . ($disabled ? 'disabled="disabled"' : '')
            . ' type="' . $field['type'] . '"'
            . (isset($field['id']) ? ' id="' . $field['id'] . '"' : '')
            . ' size="' . (isset($field['size']) ? (int) ($field['size']) : 5)
            . '" name="' . $key
            . '" value="' . ($field['type'] == 'password' ? '' : htmlentities($val, ENT_COMPAT, 'UTF-8'))
            . '" />'
            . (isset($field['next']) ? '&nbsp;' . $field['next'] : '');
    }

    /**
     * @param array<string, string|array<string>> $field
     */
    private function renderThumb(array $field): string
    {
        return "<img src=\"{$field['thumb']['file']}\" alt=\"{$field['title']}\" title=\"{$field['title']}\" style=\"float:left;\">";
    }
}
