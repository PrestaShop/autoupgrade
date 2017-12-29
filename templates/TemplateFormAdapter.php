<?php
/* 
 * 2007-2017 PrestaShop
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2017 PrestaShop SA
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

/**
 *
 * Exported from AdminSelfUpgrade
 * Temporary class in which all html content waits to be transfered into templates
 *
 * Function names can give an idea of the future template purpose, while parameter inform about
 * the data to sent to this future tpl
 */
class TemplateFormAdapter
{
    public static function displayForm(
        $defaultLanguage,
        $currentIndex,
        $name,
        $module,
        $icon,
        $tabname,
        $fields,
        $languages
    )
    {
        $html = '
		<script type="text/javascript">
			id_language = Number('.$defaultLanguage.');

			function addRemoteAddr(){
				var length = $(\'input[name=PS_MAINTENANCE_IP]\').attr(\'value\').length;
				if (length > 0)
					$(\'input[name=PS_MAINTENANCE_IP]\').attr(\'value\',$(\'input[name=PS_MAINTENANCE_IP]\').attr(\'value\') +\','.Tools14::getRemoteAddr().'\');
				else
					$(\'input[name=PS_MAINTENANCE_IP]\').attr(\'value\',\''.Tools14::getRemoteAddr().'\');
			}
		</script>
		<form action="'.$currentIndex.'&submit'.$name.$module->table.'=1&token='.$module->token.'" method="post" enctype="multipart/form-data">
			<fieldset><legend><img src="../img/admin/'.strval($icon).'.gif" />'.$tabname.'</legend>';
        
        $html .= self::displayFormFields($fields, $languages);

        if (!is_writable(_PS_ADMIN_DIR_.'/../app/config/parameters.php') and $name == 'themes') {
            $html .= '<p><img src="../img/admin/warning.gif" alt="" /> '.$this->trans('If you change the theme, the parameters.php file must be writable (CHMOD 755 / 777)', array(), 'Modules.Autoupgrade.Admin').'</p>';
        }

        $html .= '<div align="center" style="margin-top: 20px;">
					<input type="submit" value="'.$this->trans('Save', array(), 'Admin.Actions').'" name="submit'.ucfirst($name).$this->table.'" class="button" />
				</div>
			</fieldset>
		</form>';

        return $html;
    }

    public static function displayFormFields($fields, $languages)
    {
        $html = '';
        foreach ($fields as $key => $field) {
            // To move in the controller class
            $val = $this->getVal($confValues, $key);

            if (!in_array($field['type'], array('image', 'radio', 'container', 'container_end')) or isset($field['show'])) {
                $html .= '<div style="clear: both; padding-top:15px;">'.($field['title'] ? '<label >'.$field['title'].'</label>' : '').'<div class="margin-form" style="padding-top:5px;">';
            }

            /* Display the appropriate input type for each field */
            switch ($field['type']) {
                case 'disabled':
                    $html .= self::blockDisabled($field);
                    break;
                case 'select':
                    $html .= self::blockSelect($field, $key);
                    break;
                case 'selectLang':
                    $html .= self::blockSelectLang($field, $key, $languages);
                    break;
                case 'bool':
                    $html .= self::blockBool($field, $key);
                    break;
                case 'radio':
                    $html .= self::blockRadio($field, $key);
                    break;
                case 'image':
                    $html .= self::blockImage($field, $key);
                    break;
                case 'price':
                    $html .= self::blockPrice($field, $key);
                    break;
                case 'textLang':
                    $html .= self::blockTextLang($field, $key, $languages);
                    break;
                case 'file':
                    $html .= self::blockFile($field, $key);
                    break;
                case 'textarea':
                    $html .= self::blockTextArea($field, $key);
                    break;
                case 'container':
                    $html .= self::blockContainer($key);
                    break;
                case 'container_end':
                    $html .= self::blockContainerEnd($field);
                    break;
                case 'maintenance_ip':
                    $html .= self::blockMaintenanceIp($field, $key);
                    break;
                case 'text':
                default:
                    $html .= self::blockText($field, $key);
            }
            $html .=((isset($field['required']) and $field['required'] and !in_array($field['type'], array('image', 'radio')))  ? ' <sup>*</sup>' : '');
            $html .=(isset($field['desc']) ? '<p style="clear:both">'.((isset($field['thumb']) and $field['thumb'] and $field['thumb']['pos'] == 'after') ? '<img src="'.$field['thumb']['file'].'" alt="'.$field['title'].'" title="'.$field['title'].'" style="float:left;" />' : '').$field['desc'].'</p>' : '');
            if (!in_array($field['type'], array('image', 'radio', 'container', 'container_end')) or isset($field['show'])) {
                $html .= '</div></div>';
            }
        }
        return $html;
    }

        /**
      * Display flags in forms for translations
      *
      * @param array $languages All languages available
      * @param integer $defaultLanguage Default language id
      * @param string $ids Multilingual div ids in form
      * @param string $id Current div id]
      */
    public static function displayFlags($languages, $defaultLanguage, $ids, $id)
    {
        if (sizeof($languages) == 1) {
            return '';
        }
        $output = '
		<div class="displayed_flag">
			<img src="../img/l/'.$defaultLanguage.'.jpg" class="pointer" id="language_current_'.$id.'" onclick="toggleLanguageFlags(this);" alt="" />
		</div>
		<div id="languages_'.$id.'" class="language_flags">
			'.$this->trans('Choose language:', array(), 'Admin.Actions').'<br /><br />';
        foreach ($languages as $language) {
            $output .= '<img src="../img/l/'.(int)($language['id_lang']).'.jpg" class="pointer" alt="'.$language['name'].'" title="'.$language['name'].'" onclick="changeLanguage(\''.$id.'\', \''.$ids.'\', '.$language['id_lang'].', \''.$language['iso_code'].'\');" /> ';
        }
        $output .= '</div>';

        return $output;
    }

    public static function blockDisabled($field)
    {
        return $field['disabled'];
    }

    public static function blockSelect($field, $key)
    {
        $onchange = (isset($field['js']) ? ' onchange="'.$field['js'].'"' : '');
        $html = '<select name="'.$key.'"'. $onchange.' id="'.$key.'">';
        foreach ($field['list'] as $value) {
            // to be moved 
            $domValue = (isset($value['cast']) ? $value['cast']($value[$field['identifier']]) : $value[$field['identifier']]);
            $selected = (($val == $value[$field['identifier']]) ? ' selected="selected"' : '');

            $html .= '<option value="'.$domValue.'"'.$selected.'>'.$value['name'].'</option>';
        }
        $html .= '</select>';
        return $html;
    }

    public static function blockSelectLang($field, $key, $languages)
    {
        $html = '';
        foreach ($languages as $language) {
            $html .= '<div id="'.$key.'_'.$language['id_lang'].'" style="margin-bottom:8px; display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left; vertical-align: top;">
                <select name="'.$key.'_'.strtoupper($language['iso_code']).'">';
            foreach ($field['list'] as $k => $value) {
                $html .= '<option value="'.(isset($value['cast']) ? $value['cast']($value[$field['identifier']]) : $value[$field['identifier']]).'"'.((htmlentities(Tools14::getValue($key.'_'.strtoupper($language['iso_code']), (Configuration::get($key.'_'.strtoupper($language['iso_code'])) ? Configuration::get($key.'_'.strtoupper($language['iso_code'])) : '')), ENT_COMPAT, 'UTF-8') == $value[$field['identifier']]) ? ' selected="selected"' : '').'>'.$value['name'].'</option>';
            }
            $html .= '</select></div>';
        }
        return $html.self::displayFlags($languages, $defaultLanguage, $divLangName, $key);
    }

    public static function blockBool($field, $key)
    {
        return '<label class="t" for="'.$key.'_on"><img src="../img/admin/enabled.gif" alt="'.$this->trans('Yes', array(), 'Admin.Global').'" title="'.$this->trans('Yes', array(), 'Admin.Global').'" /></label>
            <input type="radio" name="'.$key.'" id="'.$key.'_on" value="1"'.($val ? ' checked="checked"' : '').(isset($field['js']['on']) ? $field['js']['on'] : '').' />
            <label class="t" for="'.$key.'_on"> '.$this->trans('Yes', array(), 'Admin.Global').'</label>
            <label class="t" for="'.$key.'_off"><img src="../img/admin/disabled.gif" alt="'.$this->trans('No', array(), 'Admin.Global').'" title="'.$this->trans('No', array(), 'Admin.Global').'" style="margin-left: 10px;" /></label>
            <input type="radio" name="'.$key.'" id="'.$key.'_off" value="0" '.(!$val ? 'checked="checked"' : '').(isset($field['js']['off']) ? $field['js']['off'] : '').'/>
            <label class="t" for="'.$key.'_off"> '.$this->trans('No', array(), 'Admin.Global').'</label>';
    }

    public static function blockRadio($field, $key)
    {
        $html = '';
        foreach ($field['choices'] as $cValue => $cKey) {
            $html .= '<input type="radio" name="'.$key.'" id="'.$key.$cValue.'_on" value="'.(int)($cValue).'"'.(($cValue == $val) ? ' checked="checked"' : '').(isset($field['js'][$cValue]) ? ' '.$field['js'][$cValue] : '').' />'
                . '<label class="t" for="'.$key.$cValue.'_on"> '.$cKey.'</label><br />';
        }
        $html .= '<br />';
        return $html;
    }

    public static function blockImage($field, $key)
    {
        $html = '<table cellspacing="0" cellpadding="0">
            <tr>';
        if ($name == 'themes') {
            $html .= '
            <td colspan="'.sizeof($field['list']).'">
                <b>'.$this->trans('In order to use a new theme, please follow these steps:', array(), 'Modules.Autoupgrade.Admin').'</b>
                <ul>
                    <li>'.$this->trans('Import your theme using this module:', array(), 'Modules.Autoupgrade.Admin').' <a href="index.php?tab=AdminModules&token='.Tools14::getAdminTokenLite('AdminModules').'&filtername=themeinstallator" style="text-decoration: underline;">'.$this->trans('Theme installer', array(), 'Modules.Autoupgrade.Admin').'</a></li>
                    <li>'.$this->trans('When your theme is imported, please select the theme in this page', array(), 'Modules.Autoupgrade.Admin').'</li>
                </ul>
            </td>
            </tr>
            <tr>
            ';
        }
        $i = 0;
        foreach ($field['list'] as $theme) {
            $html .= '<td class="center" style="width: 180px; padding:0px 20px 20px 0px;">
            <input type="radio" name="'.$key.'" id="'.$key.'_'.$theme['name'].'_on" style="vertical-align: text-bottom;" value="'.$theme['name'].'"'.
            (_THEME_NAME_ == $theme['name'] ? 'checked="checked"' : '').' />
            <label class="t" for="'.$key.'_'.$theme['name'].'_on"> '.Tools14::strtolower($theme['name']).'</label>
            <br />
            <label class="t" for="'.$key.'_'.$theme['name'].'_on">
                <img src="../themes/'.$theme['name'].'/preview.jpg" alt="'.Tools14::strtolower($theme['name']).'">
            </label>
            </td>';
            if (isset($field['max']) and ($i+1) % $field['max'] == 0) {
                $html .= '</tr><tr>';
            }
            $i++;
        }
        $html .= '</tr></table>';
        return $html;
    }

    public static function blockPrice($field, $key)
    {
        $default_currency = new Currency((int)(Configuration::get("PS_CURRENCY_DEFAULT")));
        return $default_currency->getSign('left').'<input type="'.$field['type'].'" size="'.(isset($field['size']) ? (int)($field['size']) : 5).'" name="'.$key.'" value="'.($field['type'] == 'password' ? '' : htmlentities($val, ENT_COMPAT, 'UTF-8')).'" />'.$default_currency->getSign('right').' '.$this->trans('(tax excl.)', array(), 'Admin.Global');
    }

    public static function blockTextLang($field, $key, $languages)
    {
        $html = '';
        foreach ($languages as $language) {
            $html .= '
            <div id="'.$key.'_'.$language['id_lang'].'" style="margin-bottom:8px; display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left; vertical-align: top;">
                <input type="text" size="'.(isset($field['size']) ? (int)($field['size']) : 5).'" name="'.$key.'_'.$language['id_lang'].'" value="'.htmlentities($this->getVal($confValues, $key.'_'.$language['id_lang']), ENT_COMPAT, 'UTF-8').'" />
            </div>';
        }
        return $html.self::displayFlags($languages, $defaultLanguage, $divLangName, $key);
    }

    public static function blockFile($field, $key)
    {
        $html = '';
        if (isset($field['thumb']) and $field['thumb'] and $field['thumb']['pos'] == 'before') {
            $html .= '<img src="'.$field['thumb']['file'].'" alt="'.$field['title'].'" title="'.$field['title'].'" /><br />';
        }
        $html .= '<input type="file" name="'.$key.'" />';
        return $html;
    }

    public static function blockTextArea($field, $key)
    {
        return '<textarea name='.$key.' cols="'.$field['cols'].'" rows="'.$field['rows'].'">'.htmlentities($val, ENT_COMPAT, 'UTF-8').'</textarea>';
    }

    public static function blockContainer($key)
    {
        return '<div id="'.$key.'">';
    }

    public static function blockContainerEnd($field)
    {
        return (isset($field['content']) === true ? $field['content'] : '').'</div>';
    }

    public static function blockMaintenanceIp($field, $key)
    {
        return '<input type="'.$field['type'].'"'.(isset($field['id']) === true ? ' id="'.$field['id'].'"' : '').' size="'.(isset($field['size']) ? (int)($field['size']) : 5).'" name="'.$key.'" value="'.($field['type'] == 'password' ? '' : htmlentities($val, ENT_COMPAT, 'UTF-8')).'" />'.(isset($field['next']) ? '&nbsp;'.strval($field['next']) : '').' &nbsp;<a href="#" class="button" onclick="addRemoteAddr(); return false;">'.$this->trans('Add my IP', array(), 'Modules.Autoupgrade.Admin').'</a>';
    }

    public static function blockText($field, $key)
    {
        return '<input type="'.$field['type'].'"'.(isset($field['id']) === true ? ' id="'.$field['id'].'"' : '').' size="'.(isset($field['size']) ? (int)($field['size']) : 5).'" name="'.$key.'" value="'.($field['type'] == 'password' ? '' : htmlentities($val, ENT_COMPAT, 'UTF-8')).'" />'.(isset($field['next']) ? '&nbsp;'.strval($field['next']) : '');
    }
}