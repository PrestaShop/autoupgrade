<?php
/**
* 2007-2016 PrestaShop
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2016 PrestaShop SA
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

// @since 1.4.5.0
// add the following comment in a module file to skip it in translations
// IGNORE_THIS_FILE_FOR_TRANSLATION
abstract class AdminSelfTab
{
    /** @var integer Tab id */
    public $id = -1;

    /** @var string Associated table name */
    public $table;

    /** @var string Object identifier inside the associated table */
    protected $identifier = false;

    /** @var string Tab name */
    public $name;

    /** @var string Security token */
    public $token;

    /** @var boolean Automatically join language table if true */
    public $lang = false;

    /** @var boolean Tab Automatically displays edit/delete icons if true */
    public $edit = false;

    /** @var boolean Tab Automatically displays view icon if true */
    public $view = false;

    /** @var boolean Tab Automatically displays delete icon if true */
    public $delete = false;

    /** @var boolean Table records are not deleted but marked as deleted */
    public $deleted = false;

    /** @var boolean Tab Automatically displays duplicate icon if true */
    public $duplicate = false;

    /** @var boolean Content line is clickable if true */
    public $noLink = false;

    /** @var boolean select other required fields */
    public $requiredDatabase = false;

    /** @var boolean Tab Automatically displays '$color' as background color on listing if true */
    public $colorOnBackground = false;

    /** @var string Add fields into data query to display list */
    protected $_select;

    /** @var string Join tables into data query to display list */
    protected $_join;

    /** @var string Add conditions into data query to display list */
    protected $_where;

    /** @var string Group rows into data query to display list */
    protected $_group;

    /** @var string Having rows into data query to display list */
    protected $_having;

    /** @var array Name and directory where class image are located */
    public $fieldImageSettings = array();

    /** @var string Image type */
    public $imageType = 'jpg';

    /** @var array Fields to display in list */
    public $fieldsDisplay = array();

    /** @var array Cache for query results */
    protected $_list = array();

    /** @var integer Number of results in list */
    protected $_listTotal = 0;

    /** @var string WHERE clause determined by filter fields */
    protected $_filter;

    /** @var string HAVING clause determined by filter fields */
    protected $_filterHaving;

    /** @var array Temporary SQL table WHERE clause determinated by filter fields */
    protected $_tmpTableFilter = '';

    /** @var array Number of results in list per page (used in select field) */
    protected $_pagination = array(20, 50, 100, 300);

    /** @var string ORDER BY clause determined by field/arrows in list header */
    protected $_orderBy;

    /** @var string Default ORDER BY clause when $_orderBy is not defined */
    protected $_defaultOrderBy = false;

    /** @var string Order way (ASC, DESC) determined by arrows in list header */
    protected $_orderWay;

    /** @var integer Max image size for upload */
    protected $maxImageSize = 2000000;

    /** @var array Errors displayed after post processing */
    public $_errors = array();

    /** @var array Confirmations displayed after post processing */
    protected $_conf;

    /** @var object Object corresponding to the tab */
    protected $_object = false;

    /** @var array tabAccess */
    public $tabAccess;

    /** @var string specificConfirmDelete */
    public $specificConfirmDelete = null;

    protected $identifiersDnd = array('id_product' => 'id_product', 'id_category' => 'id_category_to_move','id_cms_category' => 'id_cms_category_to_move', 'id_cms' => 'id_cms');

    /** @var bool Redirect or not ater a creation */
    protected $_redirect = true;

    protected $_languages = null;
    protected $_defaultFormLanguage = null;

    protected $_includeObj = array();
    protected $_includeVars = false;
    protected $_includeContainer = true;

    public $ajax = false;

    public static $tabParenting = array(
        'AdminProducts' => 'AdminCatalog',
        'AdminCategories' => 'AdminCatalog',
        'AdminCMS' => 'AdminCMSContent',
        'AdminCMSCategories' => 'AdminCMSContent',
        'AdminOrdersStates' => 'AdminStatuses',
        'AdminAttributeGenerator' => 'AdminProducts',
        'AdminAttributes' => 'AdminAttributesGroups',
        'AdminFeaturesValues' => 'AdminFeatures',
        'AdminReturnStates' => 'AdminStatuses',
        'AdminStatsTab' => 'AdminStats'
    );

    public function __construct()
    {
        global $cookie;
        $this->id = Tab::getCurrentTabId();
        $this->_conf = array(
        1 => $this->trans('Successful deletion.', array(), 'Admin.Notifications.Success'), 2 => $this->trans('Selection successfully deleted', array(), 'Modules.Autoupgrade.Admin'),
        3 => $this->trans('Successful creation.', array(), 'Admin.Notifications.Success'), 4 => $this->trans('Successful update.', array(), 'Admin.Notifications.Success'),
        5 => $this->trans('The new version check has been completed successfully', array(), 'Modules.Autoupgrade.Admin'), 6 => $this->trans('The settings have been successfully updated.', array(), 'Admin.Notifications.Success'),
        7 => $this->trans('The image was successfully deleted.', array(), 'Admin.Notifications.Success'), 8 => $this->trans('The module was successfully downloaded.', array(), 'Admin.Notifications.Success'),
        9 => $this->trans('The thumbnails were successfully regenerated.', array(), 'Admin.Notifications.Success'), 10 => $this->trans('Message sent to the customer', array(), 'Modules.Autoupgrade.Admin'),
        11 => $this->trans('Comment successfully added.', array(), 'Admin.Notifications.Success'), 12 => $this->trans('Module(s) installed successfully.', array(), 'Admin.Modules.Notification'),
        13 => $this->trans('Module(s) uninstalled successfully.', array(), 'Admin.Modules.Notification'), 14 => $this->trans('Language successfully copied', array(), 'Modules.Autoupgrade.Admin'),
        15 => $this->trans('The translations have been successfully added.', array(), 'Admin.International.Notification'), 16 => $this->trans('The module transplanted successfully to the hook.', array(), 'Admin.Modules.Notification'),
        17 => $this->trans('The module was successfully removed from the hook.', array(), 'Admin.Modules.Notification'), 18 => $this->trans('Successful upload.', array(), 'Admin.Notifications.Success'),
        19 => $this->trans('Duplication was completed successfully.', array(), 'Admin.Notifications.Success'), 20 => $this->trans('The translation was added successfully, but the language has not been created.', array(), 'Admin.International.Notification'),
        21 => $this->trans('Module reset successfully.', array(), 'Admin.Modules.Notification'), 22 => $this->trans('Module deleted successfully.', array(), 'Admin.Modules.Notification'),
        23 => $this->trans('Localization pack imported successfully.', array(), 'Admin.International.Notification'), 24 => $this->trans('Refund Successful', array(), 'Modules.Autoupgrade.Admin'),
        25 => $this->trans('Images successfully moved', array(), 'Modules.Autoupgrade.Admin'));
        if (!$this->identifier) {
            $this->identifier = 'id_'.$this->table;
        }
        if (!$this->_defaultOrderBy) {
            $this->_defaultOrderBy = $this->identifier;
        }
        $className = get_class($this);
        if ($className == 'AdminCategories' or $className == 'AdminProducts') {
            $className = 'AdminCatalog';
        }
        $this->token = Tools14::getAdminToken($className.(int)$this->id.(int)$cookie->id_employee);
    }


    private function getConf($fields, $languages)
    {
        $tab = array();
        foreach ($fields as $key => $field) {
            if ($field['type'] == 'textLang') {
                foreach ($languages as $language) {
                    $tab[$key.'_'.$language['id_lang']] = Tools14::getValue($key.'_'.$language['id_lang'], Configuration::get($key, $language['id_lang']));
                }
            } else {
                $tab[$key] =  Tools14::getValue($key, Configuration::get($key));
            }
        }
        $tab['__PS_BASE_URI__'] = __PS_BASE_URI__;
        $tab['_MEDIA_SERVER_1_'] = defined('_MEDIA_SERVER_1_')?_MEDIA_SERVER_1_:'';
        $tab['PS_THEME'] = _THEME_NAME_;
        if (defined('_DB_TYPE_')) {
            $tab['db_type'] = _DB_TYPE_;
        } else {
            $tab['db_type'] = 'mysql';
        }

        $tab['db_server'] = _DB_SERVER_;
        $tab['db_name'] = _DB_NAME_;
        $tab['db_prefix'] = _DB_PREFIX_;
        $tab['db_user'] = _DB_USER_;
        $tab['db_passwd'] = '';

        return $tab;
    }
    private function getDivLang($fields)
    {
        $tab = array();
        foreach ($fields as $key => $field) {
            if ($field['type'] == 'textLang' || $field['type'] == 'selectLang') {
                $tab[] = $key;
            }
        }
        return implode('¤', $tab);
    }

    private function getVal($conf, $key)
    {
        return Tools14::getValue($key, (isset($conf[$key]) ? $conf[$key] : ''));
    }

    protected function _displayForm($name, $fields, $tabname, $size, $icon)
    {
        global $currentIndex;

        $defaultLanguage = (int)(Configuration::get('PS_LANG_DEFAULT'));
        $languages = Language::getLanguages(false);
        $confValues = $this->getConf($fields, $languages);
        $divLangName = $this->getDivLang($fields);
        $required = false;

        echo '
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
		<form action="'.$currentIndex.'&submit'.$name.$this->table.'=1&token='.$this->token.'" method="post" enctype="multipart/form-data">
			<fieldset><legend><img src="../img/admin/'.strval($icon).'.gif" />'.$tabname.'</legend>';
        foreach ($fields as $key => $field) {
            /* Specific line for e-mails settings */
            if (get_class($this) == 'Adminemails' and $key == 'PS_MAIL_SERVER') {
                echo '<div id="smtp" style="display: '.((isset($confValues['PS_MAIL_METHOD']) and $confValues['PS_MAIL_METHOD'] == 2) ? 'block' : 'none').';">';
            }
            if (isset($field['required']) and $field['required']) {
                $required = true;
            }
            $val = $this->getVal($confValues, $key);

            if (!in_array($field['type'], array('image', 'radio', 'container', 'container_end')) or isset($field['show'])) {
                echo '<div style="clear: both; padding-top:15px;">'.($field['title'] ? '<label >'.$field['title'].'</label>' : '').'<div class="margin-form" style="padding-top:5px;">';
            }

            /* Display the appropriate input type for each field */
            switch ($field['type']) {
                case 'disabled': echo $field['disabled'];break;
                case 'select':
                    echo '
					<select name="'.$key.'"'.(isset($field['js']) === true ? ' onchange="'.$field['js'].'"' : '').' id="'.$key.'">';
                    foreach ($field['list'] as $k => $value) {
                        echo '<option value="'.(isset($value['cast']) ? $value['cast']($value[$field['identifier']]) : $value[$field['identifier']]).'"'.(($val == $value[$field['identifier']]) ? ' selected="selected"' : '').'>'.$value['name'].'</option>';
                    }
                    echo '
					</select>';
                    break;

                case 'selectLang':
                    foreach ($languages as $language) {
                        echo '
						<div id="'.$key.'_'.$language['id_lang'].'" style="margin-bottom:8px; display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left; vertical-align: top;">
							<select name="'.$key.'_'.strtoupper($language['iso_code']).'">';
                        foreach ($field['list'] as $k => $value) {
                            echo '<option value="'.(isset($value['cast']) ? $value['cast']($value[$field['identifier']]) : $value[$field['identifier']]).'"'.((htmlentities(Tools14::getValue($key.'_'.strtoupper($language['iso_code']), (Configuration::get($key.'_'.strtoupper($language['iso_code'])) ? Configuration::get($key.'_'.strtoupper($language['iso_code'])) : '')), ENT_COMPAT, 'UTF-8') == $value[$field['identifier']]) ? ' selected="selected"' : '').'>'.$value['name'].'</option>';
                        }
                        echo '
							</select>
						</div>';
                    }
                    $this->displayFlags($languages, $defaultLanguage, $divLangName, $key);
                    break;

                case 'bool':
                    echo '<label class="t" for="'.$key.'_on"><img src="../img/admin/enabled.gif" alt="'.$this->trans('Yes', array(), 'Admin.Global').'" title="'.$this->trans('Yes', array(), 'Admin.Global').'" /></label>
					<input type="radio" name="'.$key.'" id="'.$key.'_on" value="1"'.($val ? ' checked="checked"' : '').(isset($field['js']['on']) ? $field['js']['on'] : '').' />
					<label class="t" for="'.$key.'_on"> '.$this->trans('Yes', array(), 'Admin.Global').'</label>
					<label class="t" for="'.$key.'_off"><img src="../img/admin/disabled.gif" alt="'.$this->trans('No', array(), 'Admin.Global').'" title="'.$this->trans('No', array(), 'Admin.Global').'" style="margin-left: 10px;" /></label>
					<input type="radio" name="'.$key.'" id="'.$key.'_off" value="0" '.(!$val ? 'checked="checked"' : '').(isset($field['js']['off']) ? $field['js']['off'] : '').'/>
					<label class="t" for="'.$key.'_off"> '.$this->trans('No', array(), 'Admin.Global').'</label>';
                    break;

                case 'radio':
                    foreach ($field['choices'] as $cValue => $cKey) {
                        echo '<input type="radio" name="'.$key.'" id="'.$key.$cValue.'_on" value="'.(int)($cValue).'"'.(($cValue == $val) ? ' checked="checked"' : '').(isset($field['js'][$cValue]) ? ' '.$field['js'][$cValue] : '').' /><label class="t" for="'.$key.$cValue.'_on"> '.$cKey.'</label><br />';
                    }
                    echo '<br />';
                    break;

                case 'image':
                    echo '
					<table cellspacing="0" cellpadding="0">
						<tr>';
                    if ($name == 'themes') {
                        echo '
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
                        echo '<td class="center" style="width: 180px; padding:0px 20px 20px 0px;">
						<input type="radio" name="'.$key.'" id="'.$key.'_'.$theme['name'].'_on" style="vertical-align: text-bottom;" value="'.$theme['name'].'"'.
                        (_THEME_NAME_ == $theme['name'] ? 'checked="checked"' : '').' />
						<label class="t" for="'.$key.'_'.$theme['name'].'_on"> '.Tools14::strtolower($theme['name']).'</label>
						<br />
						<label class="t" for="'.$key.'_'.$theme['name'].'_on">
							<img src="../themes/'.$theme['name'].'/preview.jpg" alt="'.Tools14::strtolower($theme['name']).'">
						</label>
						</td>';
                        if (isset($field['max']) and ($i+1) % $field['max'] == 0) {
                            echo '</tr><tr>';
                        }
                        $i++;
                    }
                    echo '</tr>
					</table>';
                    break;

                case 'price':
                    $default_currency = new Currency((int)(Configuration::get("PS_CURRENCY_DEFAULT")));
                    echo $default_currency->getSign('left').'<input type="'.$field['type'].'" size="'.(isset($field['size']) ? (int)($field['size']) : 5).'" name="'.$key.'" value="'.($field['type'] == 'password' ? '' : htmlentities($val, ENT_COMPAT, 'UTF-8')).'" />'.$default_currency->getSign('right').' '.$this->trans('(tax excl.)', array(), 'Admin.Global');
                    break;

                case 'textLang':
                    foreach ($languages as $language) {
                        echo '
						<div id="'.$key.'_'.$language['id_lang'].'" style="margin-bottom:8px; display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left; vertical-align: top;">
							<input type="text" size="'.(isset($field['size']) ? (int)($field['size']) : 5).'" name="'.$key.'_'.$language['id_lang'].'" value="'.htmlentities($this->getVal($confValues, $key.'_'.$language['id_lang']), ENT_COMPAT, 'UTF-8').'" />
						</div>';
                    }
                    $this->displayFlags($languages, $defaultLanguage, $divLangName, $key);
                    break;

                case 'file':
                    if (isset($field['thumb']) and $field['thumb'] and $field['thumb']['pos'] == 'before') {
                        echo '<img src="'.$field['thumb']['file'].'" alt="'.$field['title'].'" title="'.$field['title'].'" /><br />';
                    }
                    echo '<input type="file" name="'.$key.'" />';
                    break;

                case 'textarea':
                    echo '<textarea name='.$key.' cols="'.$field['cols'].'" rows="'.$field['rows'].'">'.htmlentities($val, ENT_COMPAT, 'UTF-8').'</textarea>';
                    break;

                case 'container':
                    echo '<div id="'.$key.'">';
                break;

                case 'container_end':
                    echo(isset($field['content']) === true ? $field['content'] : '').'</div>';
                break;

                case 'maintenance_ip':
                    echo '<input type="'.$field['type'].'"'.(isset($field['id']) === true ? ' id="'.$field['id'].'"' : '').' size="'.(isset($field['size']) ? (int)($field['size']) : 5).'" name="'.$key.'" value="'.($field['type'] == 'password' ? '' : htmlentities($val, ENT_COMPAT, 'UTF-8')).'" />'.(isset($field['next']) ? '&nbsp;'.strval($field['next']) : '').' &nbsp;<a href="#" class="button" onclick="addRemoteAddr(); return false;">'.$this->trans('Add my IP', array(), 'Modules.Autoupgrade.Admin').'</a>';
                break;

                case 'text':
                default:
                    echo '<input type="'.$field['type'].'"'.(isset($field['id']) === true ? ' id="'.$field['id'].'"' : '').' size="'.(isset($field['size']) ? (int)($field['size']) : 5).'" name="'.$key.'" value="'.($field['type'] == 'password' ? '' : htmlentities($val, ENT_COMPAT, 'UTF-8')).'" />'.(isset($field['next']) ? '&nbsp;'.strval($field['next']) : '');
            }
            echo((isset($field['required']) and $field['required'] and !in_array($field['type'], array('image', 'radio')))  ? ' <sup>*</sup>' : '');
            echo(isset($field['desc']) ? '<p style="clear:both">'.((isset($field['thumb']) and $field['thumb'] and $field['thumb']['pos'] == 'after') ? '<img src="'.$field['thumb']['file'].'" alt="'.$field['title'].'" title="'.$field['title'].'" style="float:left;" />' : '').$field['desc'].'</p>' : '');
            if (!in_array($field['type'], array('image', 'radio', 'container', 'container_end')) or isset($field['show'])) {
                echo '</div></div>';
            }
        }

        /* End of specific div for e-mails settings */
        if (get_class($this) == 'Adminemails') {
            echo '<script type="text/javascript">if (getE(\'PS_MAIL_METHOD2_on\').checked) getE(\'smtp\').style.display = \'block\'; else getE(\'smtp\').style.display = \'none\';</script></div>';
        }

        if (!is_writable(_PS_ADMIN_DIR_.'/../app/config/parameters.php') and $name == 'themes') {
            echo '<p><img src="../img/admin/warning.gif" alt="" /> '.$this->trans('If you change the theme, the parameters.php file must be writable (CHMOD 755 / 777)', array(), 'Modules.Autoupgrade.Admin').'</p>';
        }

        echo '	<div align="center" style="margin-top: 20px;">
					<input type="submit" value="'.$this->trans('Save', array(), 'Admin.Actions').'" name="submit'.ucfirst($name).$this->table.'" class="button" />
				</div>
				'.($required ? '<div class="small"><sup>*</sup> '.$this->trans('Required field', array(), 'Modules.Autoupgrade.Admin').'</div>' : '').'
			</fieldset>
		</form>';
    }

    /**
     * use translations files to replace english expression.
     *
     * @param mixed $string term or expression in english
     * @param string $class
     * @param boolan $addslashes if set to true, the return value will pass through addslashes(). Otherwise, stripslashes().
     * @param boolean $htmlentities if set to true(default), the return value will pass through htmlentities($string, ENT_QUOTES, 'utf-8')
     * @return string the translation if available, or the english default text.
     */
    protected function l($string, $class = 'AdminTab', $addslashes = false, $htmlentities = true)
    {
        global $_LANGADM;
        if (empty($_LANGADM)) {
            $_LANGADM = array();
        }
        // if the class is extended by a module, use modules/[module_name]/xx.php lang file
        $currentClass = get_class($this);
        if (class_exists('Module') and method_exists('Module', 'getModuleNameFromClass')) {
            if (Module::getModuleNameFromClass($currentClass)) {
                $string = str_replace('\'', '\\\'', $string);
                return Module::findTranslation(Module::$classInModule[$currentClass], $string, $currentClass);
            }
        }

        if ($class == __CLASS__) {
            $class = 'AdminTab';
        }

        $key = md5(str_replace('\'', '\\\'', $string));
        $str = (key_exists(get_class($this).$key, $_LANGADM)) ? $_LANGADM[get_class($this).$key] : ((key_exists($class.$key, $_LANGADM)) ? $_LANGADM[$class.$key] : $string);
        $str = $htmlentities ? htmlentities($str, ENT_QUOTES, 'utf-8') : $str;
        return str_replace('"', '&quot;', ($addslashes ? addslashes($str) : stripslashes($str)));
    }

    public function getTranslator()
    {
        return Context::getContext()->getTranslator();
    }

    protected function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        $parameters['legacy'] = 'htmlspecialchars';
        return $this->getTranslator()->trans($id, $parameters, $domain, $locale);
    }

    /**
     * ajaxDisplay is the default ajax return sytem
     *
     * @return void
     */
    public function displayAjax()
    {
    }
    /**
     * Manage page display (form, list...)
     *
     * @global string $currentIndex Current URL in order to keep current Tab
     */
    public function display()
    {
        global $currentIndex, $cookie;

        // Include other tab in current tab
        if ($this->includeSubTab('display', array('submitAdd2', 'add', 'update', 'view'))) {
        }

        // Include current tab
        elseif ((Tools14::getValue('submitAdd'.$this->table) and sizeof($this->_errors)) or isset($_GET['add'.$this->table])) {
            if ($this->tabAccess['add'] === '1') {
                $this->displayForm();
                if ($this->tabAccess['view']) {
                    echo '<br /><br /><a href="'.((Tools14::getValue('back')) ? Tools14::getValue('back') : $currentIndex.'&token='.$this->token).'"><img src="../img/admin/arrow2.gif" /> '.((Tools14::getValue('back')) ? $this->trans('Back', array(), 'Modules.Autoupgrade.Admin') : $this->trans('Back to list', array(), 'Admin.Actions')).'</a><br />';
                }
            } else {
                echo $this->trans('You do not have permission to add here', array(), 'Modules.Autoupgrade.Admin');
            }
        } elseif (isset($_GET['update'.$this->table])) {
            if ($this->tabAccess['edit'] === '1' or ($this->table == 'employee' and $cookie->id_employee == Tools14::getValue('id_employee'))) {
                $this->displayForm();
                if ($this->tabAccess['view']) {
                    echo '<br /><br /><a href="'.((Tools14::getValue('back')) ? Tools14::getValue('back') : $currentIndex.'&token='.$this->token).'"><img src="../img/admin/arrow2.gif" /> '.((Tools14::getValue('back')) ? $this->trans('Back', array(), 'Modules.Autoupgrade.Admin') : $this->trans('Back to list', array(), 'Admin.Actions')).'</a><br />';
                }
            } else {
                echo $this->trans('You do not have permission to edit this.', array(), 'Admin.Notifications.Error');
            }
        } elseif (isset($_GET['view'.$this->table])) {
            $this->{'view'.$this->table}();
        } else {
            $this->getList((int)($cookie->id_lang));
            $this->displayList();
            $this->displayOptionsList();
            $this->displayRequiredFields();
            $this->includeSubTab('display');
        }
    }

    public function displayRequiredFields()
    {
        global $currentIndex;
        if (!$this->tabAccess['add'] or !$this->tabAccess['delete'] === '1' or !$this->requiredDatabase) {
            return;
        }
        $rules = call_user_func_array(array($this->className, 'getValidationRules'), array($this->className));
        $required_class_fields = array($this->identifier);
        foreach ($rules['required'] as $required) {
            $required_class_fields[] = $required;
        }

        echo '<br />
		<p><a href="#" onclick="if ($(\'.requiredFieldsParameters:visible\').length == 0) $(\'.requiredFieldsParameters\').slideDown(\'slow\'); else $(\'.requiredFieldsParameters\').slideUp(\'slow\'); return false;"><img src="../img/admin/duplicate.gif" alt="" /> '.$this->trans('Set required fields for this section', array(), 'Modules.Autoupgrade.Admin').'</a></p>
		<fieldset style="display:none" class="width1 requiredFieldsParameters">
		<legend>'.$this->trans('Required Fields', array(), 'Modules.Autoupgrade.Admin').'</legend>
		<form name="updateFields" action="'.$currentIndex.'&submitFields'.$this->table.'=1&token='.$this->token.'" method="post">
		<p><b>'.$this->trans('Select the fields you would like to be required for this section.', array(), 'Modules.Autoupgrade.Admin').'<br />
		<table cellspacing="0" cellpadding="0" class="table width1 clear">
		<tr>
			<th><input type="checkbox" onclick="checkDelBoxes(this.form, \'fieldsBox[]\', this.checked)" class="noborder" name="checkme"></th>
			<th>'.$this->trans('Field Name', array(), 'Modules.Autoupgrade.Admin').'</th>
		</tr>';

        $object = new $this->className();
        $res = $object->getFieldsRequiredDatabase();

        $required_fields = array();
        foreach ($res as $row) {
            $required_fields[(int)$row['id_required_field']] = $row['field_name'];
        }


        $table_fields = Db::getInstance()->ExecuteS('SHOW COLUMNS FROM '.pSQL(_DB_PREFIX_.$this->table));
        $irow = 0;
        foreach ($table_fields as $field) {
            if (in_array($field['Field'], $required_class_fields)) {
                continue;
            }
            echo '<tr class="'.($irow++ % 2 ? 'alt_row' : '').'">
						<td class="noborder"><input type="checkbox" name="fieldsBox[]" value="'.$field['Field'].'" '.(in_array($field['Field'], $required_fields) ? 'checked="checked"' : '').' /></td>
						<td>'.$field['Field'].'</td>
					</tr>';
        }
        echo '</table><br />
				<center><input style="margin-left:15px;" class="button" type="submit" value="'.$this->trans('Save', array(), 'Admin.Actions').'" name="submitFields" /></center>
		</fieldset>';
    }

    public function includeSubTab($methodname, $actions = array())
    {
        if (!isset($this->_includeTab) or !is_array($this->_includeTab)) {
            return false;
        }
        $key = 0;
        $inc = false;
        foreach ($this->_includeTab as $subtab => $extraVars) {
            /* New tab loading */
            $classname = 'Admin'.$subtab;
            if ($module = Db::getInstance()->getValue('SELECT `module` FROM `'._DB_PREFIX_.'tab` WHERE `class_name` = \''.pSQL($classname).'\'') and file_exists(_PS_MODULE_DIR_.'/'.$module.'/'.$classname.'.php')) {
                include_once(_PS_MODULE_DIR_.'/'.$module.'/'.$classname.'.php');
            } elseif (file_exists(_PS_ADMIN_DIR_.'/tabs/'.$classname.'.php')) {
                include_once('tabs/'.$classname.'.php');
            }
            if (!isset($this->_includeObj[$key])) {
                $this->_includeObj[$key] = new $classname;
            }
            $adminTab = $this->_includeObj[$key];
            $adminTab->token = $this->token;

            /* Extra variables addition */
            if (!empty($extraVars) and is_array($extraVars)) {
                foreach ($extraVars as $varKey => $varValue) {
                    $adminTab->$varKey = $varValue;
                }
            }

            /* Actions management */
            foreach ($actions as $action) {
                switch ($action) {

                    case 'submitAdd1':
                        if (Tools14::getValue('submitAdd'.$adminTab->table)) {
                            $ok_inc = true;
                        }
                        break;
                    case 'submitAdd2':
                        if (Tools14::getValue('submitAdd'.$adminTab->table) and sizeof($adminTab->_errors)) {
                            $ok_inc = true;
                        }
                        break;
                    case 'submitDel':
                        if (Tools14::getValue('submitDel'.$adminTab->table)) {
                            $ok_inc = true;
                        }
                        break;
                    case 'submitFilter':
                        if (Tools14::isSubmit('submitFilter'.$adminTab->table)) {
                            $ok_inc = true;
                        }
                    case 'submitReset':
                        if (Tools14::isSubmit('submitReset'.$adminTab->table)) {
                            $ok_inc = true;
                        }
                    default:
                        if (isset($_GET[$action.$adminTab->table])) {
                            $ok_inc = true;
                        }
                }
            }
            $inc = false;
            if ((isset($ok_inc) and $ok_inc) or !sizeof($actions)) {
                if (!$adminTab->viewAccess()) {
                    echo $this->trans('Access denied', array(), 'Admin.Notifications.Error');
                    return false;
                }
                if (!sizeof($actions)) {
                    if (($methodname == 'displayErrors' and sizeof($adminTab->_errors)) or $methodname != 'displayErrors') {
                        echo(isset($this->_includeTabTitle[$key]) ? '<h2>'.$this->_includeTabTitle[$key].'</h2>' : '');
                    }
                }
                if ($adminTab->_includeVars) {
                    foreach ($adminTab->_includeVars as $var => $value) {
                        $adminTab->$var = $this->$value;
                    }
                }
                $adminTab->$methodname();
                $inc = true;
            }
            $key++;
        }
        return $inc;
    }

    /**
     * Manage page display (form, list...)
     *
     * @param string $className Allow to validate a different class than the current one
     */
    public function validateRules($className = false)
    {
        if (!$className) {
            $className = $this->className;
        }

        /* Class specific validation rules */
        $rules = call_user_func(array($className, 'getValidationRules'), $className);

        if ((sizeof($rules['requiredLang']) or sizeof($rules['sizeLang']) or sizeof($rules['validateLang']))) {
            /* Language() instance determined by default language */
            $defaultLanguage = new Language((int)(Configuration::get('PS_LANG_DEFAULT')));

            /* All availables languages */
            $languages = Language::getLanguages(false);
        }

        /* Checking for required fields */
        foreach ($rules['required'] as $field) {
            if (($value = Tools14::getValue($field)) == false and (string)$value != '0') {
                if (!Tools14::getValue($this->identifier) or ($field != 'passwd' and $field != 'no-picture')) {
                    $this->_errors[] = $this->trans('The field named %s is required.', array(call_user_func(array($className, 'displayFieldName'), $field, $className)), 'Modules.Autoupgrade.Admin');
                }
            }
        }

        /* Checking for multilingual required fields */
        foreach ($rules['requiredLang'] as $fieldLang) {
            if (($empty = Tools14::getValue($fieldLang.'_'.$defaultLanguage->id)) === false or $empty !== '0' and empty($empty)) {
                $this->_errors[] = $this->trans(
                    'The field named %fieldname% is required at least in the %languagename% language.',
                    array(
                        '%fieldname%' => call_user_func(array($className, 'displayFieldName'), $fieldLang, $className),
                        '%languagename%' => $defaultLanguage->name,
                    ),
                    'Modules.Autoupgrade.Admin'
                );
            }
        }

        /* Checking for maximum fields sizes */
        foreach ($rules['size'] as $field => $maxLength) {
            if (Tools14::getValue($field) !== false and Tools14::strlen(Tools14::getValue($field)) > $maxLength) {
                $this->_errors[] = $this->trans(
                    'The field named %fieldname% is too long (%charsnumber% chars max).',
                    array(
                        '%fieldname%' => call_user_func(array($className, 'displayFieldName'), $field, $className),
                        '%charsnumber%' => $maxLength,
                    ),
                    'Modules.Autoupgrade.Admin'
                );
            }
        }

        /* Checking for maximum multilingual fields size */
        foreach ($rules['sizeLang'] as $fieldLang => $maxLength) {
            foreach ($languages as $language) {
                if (Tools14::getValue($fieldLang.'_'.$language['id_lang']) !== false and Tools14::strlen(Tools14::getValue($fieldLang.'_'.$language['id_lang'])) > $maxLength) {
                    $this->_errors[] = $this->trans(
                        'Your entry in field %fieldname% (language %languagename%) exceeds max length %charsnumber% chars (incl. HTML tags).',
                        array(
                            '%fieldname%' => call_user_func(array($className, 'displayFieldName'), $fieldLang, $className),
                            '%languagename%' => $language['name'],
                            '%charsnumber%' => $maxLength,
                        ),
                        'Modules.Autoupgrade.Admin'
                    );
                }
            }
        }

        /* Overload this method for custom checking */
        $this->_childValidation();

        /* Checking for fields validity */
        foreach ($rules['validate'] as $field => $function) {
            if (($value = Tools14::getValue($field)) !== false and ($field != 'passwd')) {
                if (!Validate::$function($value)) {
                    $this->_errors[] = $this->trans('The %s field is invalid.', array('<b>'.call_user_func(array($className, 'displayFieldName'), $field, $className).'</b>'), 'Admin.Notifications.Error');
                }
            }
        }

        /* Checking for passwd_old validity */
        if (($value = Tools14::getValue('passwd')) != false) {
            if ($className == 'Employee' and !Validate::isPasswdAdmin($value)) {
                $this->_errors[] = $this->trans('The %s field is invalid.', array('<b>'.call_user_func(array($className, 'displayFieldName'), 'passwd', $className).'</b>'), 'Admin.Notifications.Error');
            } elseif ($className == 'Customer' and !Validate::isPasswd($value)) {
                $this->_errors[] = $this->trans('The %s field is invalid.', array('<b>'.call_user_func(array($className, 'displayFieldName'), 'passwd', $className).'</b>'), 'Admin.Notifications.Error');
            }
        }

        /* Checking for multilingual fields validity */
        foreach ($rules['validateLang'] as $fieldLang => $function) {
            foreach ($languages as $language) {
                if (($value = Tools14::getValue($fieldLang.'_'.$language['id_lang'])) !== false and !empty($value)) {
                    if (!Validate::$function($value)) {
                        $this->_errors[] = $this->trans('The %s field is invalid.', array('<b>'.call_user_func(array($className, 'displayFieldName'), $fieldLang, $className).' ('.$language['name'].')</b>'), 'Admin.Notifications.Error');
                    }
                }
            }
        }
    }

    /**
     * Overload this method for custom checking
     */
    protected function _childValidation()
    {
    }

    /**
     * Overload this method for custom checking
     *
     * @param integer $id Object id used for deleting images
     * TODO This function will soon be deprecated. Use ObjectModel->deleteImage instead.
     */
    public function deleteImage($id)
    {
        $dir = null;
        /* Deleting object images and thumbnails (cache) */
        if (key_exists('dir', $this->fieldImageSettings)) {
            $dir = $this->fieldImageSettings['dir'].'/';
            if (file_exists(_PS_IMG_DIR_.$dir.$id.'.'.$this->imageType) and !unlink(_PS_IMG_DIR_.$dir.$id.'.'.$this->imageType)) {
                return false;
            }
        }
        if (file_exists(_PS_TMP_IMG_DIR_.$this->table.'_'.$id.'.'.$this->imageType) and !unlink(_PS_TMP_IMG_DIR_.$this->table.'_'.$id.'.'.$this->imageType)) {
            return false;
        }
        if (file_exists(_PS_TMP_IMG_DIR_.$this->table.'_mini_'.$id.'.'.$this->imageType) and !unlink(_PS_TMP_IMG_DIR_.$this->table.'_mini_'.$id.'.'.$this->imageType)) {
            return false;
        }
        $types = ImageType::getImagesTypes();
        foreach ($types as $imageType) {
            if (file_exists(_PS_IMG_DIR_.$dir.$id.'-'.stripslashes($imageType['name']).'.'.$this->imageType) and !unlink(_PS_IMG_DIR_.$dir.$id.'-'.stripslashes($imageType['name']).'.'.$this->imageType)) {
                return false;
            }
        }
        return true;
    }

    /**
     * ajaxPreProcess is a method called in ajax-tab.php before displayConf().
     *
     * @return void
     */
    public function ajaxPreProcess()
    {
    }

    /**
     * ajaxProcess is the default handle method for request with ajax-tab.php
     *
     * @return void
     */
    public function ajaxProcess()
    {
    }

    /**
     * Manage page processing
     *
     * @global string $currentIndex Current URL in order to keep current Tab
     */
    public function postProcess()
    {
        global $currentIndex, $cookie;
        if (!isset($this->table)) {
            return false;
        }

        // set token
        $token = Tools14::getValue('token') ? Tools14::getValue('token') : $this->token;

        // Sub included tab postProcessing
        $this->includeSubTab('postProcess', array('status', 'submitAdd1', 'submitDel', 'delete', 'submitFilter', 'submitReset'));

        /* Delete object image */
        if (isset($_GET['deleteImage'])) {
            if (Validate::isLoadedObject($object = $this->loadObject())) {
                if (($object->deleteImage())) {
                    Tools14::redirectAdmin($currentIndex.'&add'.$this->table.'&'.$this->identifier.'='.Tools14::getValue($this->identifier).'&conf=7&token='.$token);
                }
            }
            $this->_errors[] = $this->trans('An error occurred during image deletion (cannot load object).', array(), 'Modules.Autoupgrade.Admin');
        }

        /* Delete object */
        elseif (isset($_GET['delete'.$this->table])) {
            if ($this->tabAccess['delete'] === '1') {
                if (Validate::isLoadedObject($object = $this->loadObject()) and isset($this->fieldImageSettings)) {
                    // check if request at least one object with noZeroObject
                    if (isset($object->noZeroObject) and sizeof(call_user_func(array($this->className, $object->noZeroObject))) <= 1) {
                        $this->_errors[] = $this->trans('You need at least one object.', array(), 'Modules.Autoupgrade.Admin').' <b>'.$this->table.'</b><br />'.$this->trans('You cannot delete all of the items.', array(), 'Modules.Autoupgrade.Admin');
                    } else {
                        if ($this->deleted) {
                            $object->deleteImage();
                            $object->deleted = 1;
                            if ($object->update()) {
                                Tools14::redirectAdmin($currentIndex.'&conf=1&token='.$token);
                            }
                        } elseif ($object->delete()) {
                            Tools14::redirectAdmin($currentIndex.'&conf=1&token='.$token);
                        }
                        $this->_errors[] = $this->trans('An error occurred during deletion.', array(), 'Modules.Autoupgrade.Admin');
                    }
                } else {
                    $this->_errors[] = $this->trans('An error occurred while deleting the object.', array(), 'Admin.Notifications.Error').' <b>'.$this->table.'</b> '.$this->trans('(cannot load object)', array(), 'Modules.Autoupgrade.Admin');
                }
            } else {
                $this->_errors[] = $this->trans('You do not have permission to delete this.', array(), 'Admin.Notifications.Error');
            }
        }

        /* Change object statuts (active, inactive) */
        elseif ((isset($_GET['status'.$this->table]) or isset($_GET['status'])) and Tools14::getValue($this->identifier)) {
            if ($this->tabAccess['edit'] === '1') {
                if (Validate::isLoadedObject($object = $this->loadObject())) {
                    if ($object->toggleStatus()) {
                        Tools14::redirectAdmin($currentIndex.'&conf=5'.((($id_category = (int)(Tools14::getValue('id_category'))) and Tools14::getValue('id_product')) ? '&id_category='.$id_category : '').'&token='.$token);
                    } else {
                        $this->_errors[] = $this->trans('An error occurred while updating the status.', array(), 'Admin.Notifications.Error');
                    }
                } else {
                    $this->_errors[] = $this->trans('An error occurred while updating the status for an object.', array(), 'Admin.Catalog.Notification').' <b>'.$this->table.'</b> '.$this->trans('(cannot load object)', array(), 'Modules.Autoupgrade.Admin');
                }
            } else {
                $this->_errors[] = $this->trans('You do not have permission to edit this.', array(), 'Admin.Notifications.Error');
            }
        }
        /* Move an object */
        elseif (isset($_GET['position'])) {
            if ($this->tabAccess['edit'] !== '1') {
                $this->_errors[] = $this->trans('You do not have permission to edit this.', array(), 'Admin.Notifications.Error');
            } elseif (!Validate::isLoadedObject($object = $this->loadObject())) {
                $this->_errors[] = $this->trans('An error occurred while updating the status for an object.', array(), 'Admin.Catalog.Notification').' <b>'.$this->table.'</b> '.$this->trans('(cannot load object)', array(), 'Modules.Autoupgrade.Admin');
            } elseif (!$object->updatePosition((int)(Tools14::getValue('way')), (int)(Tools14::getValue('position')))) {
                $this->_errors[] = $this->trans('Failed to update the position.', array(), 'Admin.Notifications.Error');
            } else {
                Tools14::redirectAdmin($currentIndex.'&'.$this->table.'Orderby=position&'.$this->table.'Orderway=asc&conf=5'.(($id_category = (int)(Tools14::getValue($this->identifier))) ? ('&'.$this->identifier.'='.$id_category) : '').'&token='.$token);
            }
            Tools14::redirectAdmin($currentIndex.'&'.$this->table.'Orderby=position&'.$this->table.'Orderway=asc&conf=5'.((($id_category = (int)(Tools14::getValue('id_category'))) and Tools14::getValue('id_product')) ? '&id_category='.$id_category : '').'&token='.$token);
        }
        /* Delete multiple objects */
        elseif (Tools14::getValue('submitDel'.$this->table)) {
            if ($this->tabAccess['delete'] === '1') {
                if (isset($_POST[$this->table.'Box'])) {
                    $object = new $this->className();
                    if (isset($object->noZeroObject) and
                        // Check if all object will be deleted
                        (sizeof(call_user_func(array($this->className, $object->noZeroObject))) <= 1 or sizeof($_POST[$this->table.'Box']) == sizeof(call_user_func(array($this->className, $object->noZeroObject))))) {
                        $this->_errors[] = $this->trans('You need at least one object.', array(), 'Admin.Notifications.Error').' <b>'.$this->table.'</b><br />'.$this->trans('You cannot delete all of the items.', array(), 'Admin.Notifications.Error');
                    } else {
                        $result = true;
                        if ($this->deleted) {
                            foreach (Tools14::getValue($this->table.'Box') as $id) {
                                $toDelete = new $this->className($id);
                                $toDelete->deleted = 1;
                                $result = $result and $toDelete->update();
                            }
                        } else {
                            $result = $object->deleteSelection(Tools14::getValue($this->table.'Box'));
                        }

                        if ($result) {
                            Tools14::redirectAdmin($currentIndex.'&conf=2&token='.$token);
                        }
                        $this->_errors[] = $this->trans('An error occurred while deleting this selection.', array(), 'Admin.Notifications.Error');
                    }
                } else {
                    $this->_errors[] = $this->trans('You must select at least one element to delete.', array(), 'Admin.Notifications.Error');
                }
            } else {
                $this->_errors[] = $this->trans('You do not have permission to delete this.', array(), 'Admin.Notifications.Error');
            }
        }

        /* Create or update an object */
        elseif (Tools14::getValue('submitAdd'.$this->table)) {
            /* Checking fields validity */
            $this->validateRules();
            if (!sizeof($this->_errors)) {
                $id = (int)(Tools14::getValue($this->identifier));

                /* Object update */
                if (isset($id) and !empty($id)) {
                    if ($this->tabAccess['edit'] === '1' or ($this->table == 'employee' and $cookie->id_employee == Tools14::getValue('id_employee') and Tools14::isSubmit('updateemployee'))) {
                        $object = new $this->className($id);
                        if (Validate::isLoadedObject($object)) {
                            /* Specific to objects which must not be deleted */
                            if ($this->deleted and $this->beforeDelete($object)) {
                                // Create new one with old objet values
                                $objectNew = new $this->className($object->id);
                                $objectNew->id = null;
                                $objectNew->date_add = '';
                                $objectNew->date_upd = '';

                                // Update old object to deleted
                                $object->deleted = 1;
                                $object->update();

                                // Update new object with post values
                                $this->copyFromPost($objectNew, $this->table);
                                $result = $objectNew->add();
                                if (Validate::isLoadedObject($objectNew)) {
                                    $this->afterDelete($objectNew, $object->id);
                                }
                            } else {
                                $this->copyFromPost($object, $this->table);
                                $result = $object->update();
                                $this->afterUpdate($object);
                            }
                            if (!$result) {
                                $this->_errors[] = $this->trans('An error occurred while updating an object.', array(), 'Admin.Notifications.Error').' <b>'.$this->table.'</b> ('.Db::getInstance()->getMsgError().')';
                            } elseif ($this->postImage($object->id) and !sizeof($this->_errors)) {
                                $parent_id = (int)(Tools14::getValue('id_parent', 1));
                                // Specific back redirect
                                if ($back = Tools14::getValue('back')) {
                                    Tools14::redirectAdmin(urldecode($back).'&conf=4');
                                }
                                // Specific scene feature
                                if (Tools14::getValue('stay_here') == 'on' || Tools14::getValue('stay_here') == 'true' || Tools14::getValue('stay_here') == '1') {
                                    Tools14::redirectAdmin($currentIndex.'&'.$this->identifier.'='.$object->id.'&conf=4&updatescene&token='.$token);
                                }
                                // Save and stay on same form
                                if (Tools14::isSubmit('submitAdd'.$this->table.'AndStay')) {
                                    Tools14::redirectAdmin($currentIndex.'&'.$this->identifier.'='.$object->id.'&conf=4&update'.$this->table.'&token='.$token);
                                }
                                // Save and back to parent
                                if (Tools14::isSubmit('submitAdd'.$this->table.'AndBackToParent')) {
                                    Tools14::redirectAdmin($currentIndex.'&'.$this->identifier.'='.$parent_id.'&conf=4&token='.$token);
                                }
                                // Default behavior (save and back)
                                Tools14::redirectAdmin($currentIndex.($parent_id ? '&'.$this->identifier.'='.$object->id : '').'&conf=4&token='.$token);
                            }
                        } else {
                            $this->_errors[] = $this->trans('An error occurred while updating an object.', array(), 'Admin.Notifications.Error').' <b>'.$this->table.'</b> '.$this->trans('(cannot load object)', array(), 'Modules.Autoupgrade.Admin');
                        }
                    } else {
                        $this->_errors[] = $this->trans('You do not have permission to edit this.', array(), 'Admin.Notifications.Error');
                    }
                }

                /* Object creation */
                else {
                    if ($this->tabAccess['add'] === '1') {
                        $object = new $this->className();
                        $this->copyFromPost($object, $this->table);
                        if (!$object->add()) {
                            $this->_errors[] = $this->trans('An error occurred while creating an object.', array(), 'Admin.Notifications.Error').' <b>'.$this->table.' ('.mysql_error().')</b>';
                        } elseif (($_POST[$this->identifier] = $object->id /* voluntary */) and $this->postImage($object->id) and !sizeof($this->_errors) and $this->_redirect) {
                            $parent_id = (int)(Tools14::getValue('id_parent', 1));
                            $this->afterAdd($object);
                            // Save and stay on same form
                            if (Tools14::isSubmit('submitAdd'.$this->table.'AndStay')) {
                                Tools14::redirectAdmin($currentIndex.'&'.$this->identifier.'='.$object->id.'&conf=3&update'.$this->table.'&token='.$token);
                            }
                            // Save and back to parent
                            if (Tools14::isSubmit('submitAdd'.$this->table.'AndBackToParent')) {
                                Tools14::redirectAdmin($currentIndex.'&'.$this->identifier.'='.$parent_id.'&conf=3&token='.$token);
                            }
                            // Default behavior (save and back)
                            Tools14::redirectAdmin($currentIndex.($parent_id ? '&'.$this->identifier.'='.$object->id : '').'&conf=3&token='.$token);
                        }
                    } else {
                        $this->_errors[] = $this->trans('You do not have permission to add here.', array(), 'Modules.Autoupgrade.Admin');
                    }
                }
            }
            $this->_errors = array_unique($this->_errors);
        }

        /* Cancel all filters for this tab */
        elseif (isset($_POST['submitReset'.$this->table])) {
            $filters = $cookie->getFamily($this->table.'Filter_');
            foreach ($filters as $cookieKey => $filter) {
                if (strncmp($cookieKey, $this->table.'Filter_', 7 + Tools14::strlen($this->table)) == 0) {
                    $key = substr($cookieKey, 7 + Tools14::strlen($this->table));
                        /* Table alias could be specified using a ! eg. alias!field */
                        $tmpTab = explode('!', $key);
                    $key = (count($tmpTab) > 1 ? $tmpTab[1] : $tmpTab[0]);
                    if (array_key_exists($key, $this->fieldsDisplay)) {
                        unset($cookie->$cookieKey);
                    }
                }
            }
            if (isset($cookie->{'submitFilter'.$this->table})) {
                unset($cookie->{'submitFilter'.$this->table});
            }
            if (isset($cookie->{$this->table.'Orderby'})) {
                unset($cookie->{$this->table.'Orderby'});
            }
            if (isset($cookie->{$this->table.'Orderway'})) {
                unset($cookie->{$this->table.'Orderway'});
            }
            unset($_POST);
        }

        /* Submit options list */
        elseif (Tools14::getValue('submitOptions'.$this->table)) {
            $this->updateOptions($token);
        }

        /* Manage list filtering */
        elseif (Tools14::isSubmit('submitFilter'.$this->table) or $cookie->{'submitFilter'.$this->table} !== false) {
            $_POST = array_merge($cookie->getFamily($this->table.'Filter_'), (isset($_POST) ? $_POST : array()));
            foreach ($_POST as $key => $value) {
                /* Extracting filters from $_POST on key filter_ */
                if ($value != null and !strncmp($key, $this->table.'Filter_', 7 + Tools14::strlen($this->table))) {
                    $key = Tools14::substr($key, 7 + Tools14::strlen($this->table));
                    /* Table alias could be specified using a ! eg. alias!field */
                    $tmpTab = explode('!', $key);
                    $filter = count($tmpTab) > 1 ? $tmpTab[1] : $tmpTab[0];
                    if ($field = $this->filterToField($key, $filter)) {
                        $type = (array_key_exists('filter_type', $field) ? $field['filter_type'] : (array_key_exists('type', $field) ? $field['type'] : false));
                        if (($type == 'date' or $type == 'datetime') and is_string($value)) {
                            $value = unserialize($value);
                        }
                        $key = isset($tmpTab[1]) ? $tmpTab[0].'.`'.$tmpTab[1].'`' : '`'.$tmpTab[0].'`';
                        if (array_key_exists('tmpTableFilter', $field)) {
                            $sqlFilter = & $this->_tmpTableFilter;
                        } elseif (array_key_exists('havingFilter', $field)) {
                            $sqlFilter = & $this->_filterHaving;
                        } else {
                            $sqlFilter = & $this->_filter;
                        }

                        /* Only for date filtering (from, to) */
                        if (is_array($value)) {
                            if (isset($value[0]) and !empty($value[0])) {
                                if (!Validate::isDate($value[0])) {
                                    $this->_errors[] = $this->trans('The \'From\' date format is invalid (YYYY-MM-DD)', array(), 'Admin.Notifications.Error');
                                } else {
                                    $sqlFilter .= ' AND '.pSQL($key).' >= \''.pSQL(Tools14::dateFrom($value[0])).'\'';
                                }
                            }

                            if (isset($value[1]) and !empty($value[1])) {
                                if (!Validate::isDate($value[1])) {
                                    $this->_errors[] = $this->trans('The \'To\' date format is invalid (YYYY-MM-DD)', array(), 'Admin.Notifications.Error');
                                } else {
                                    $sqlFilter .= ' AND '.pSQL($key).' <= \''.pSQL(Tools14::dateTo($value[1])).'\'';
                                }
                            }
                        } else {
                            $sqlFilter .= ' AND ';
                            if ($type == 'int' or $type == 'bool') {
                                $sqlFilter .= (($key == $this->identifier or $key == '`'.$this->identifier.'`' or $key == '`active`') ? 'a.' : '').pSQL($key).' = '.(int)($value).' ';
                            } elseif ($type == 'decimal') {
                                $sqlFilter .= (($key == $this->identifier or $key == '`'.$this->identifier.'`') ? 'a.' : '').pSQL($key).' = '.(float)($value).' ';
                            } elseif ($type == 'select') {
                                $sqlFilter .= (($key == $this->identifier or $key == '`'.$this->identifier.'`') ? 'a.' : '').pSQL($key).' = \''.pSQL($value).'\' ';
                            } else {
                                $sqlFilter .= (($key == $this->identifier or $key == '`'.$this->identifier.'`') ? 'a.' : '').pSQL($key).' LIKE \'%'.pSQL($value).'%\' ';
                            }
                        }
                    }
                }
            }
        } elseif (Tools14::isSubmit('submitFields') and $this->requiredDatabase and $this->tabAccess['add'] === '1' and $this->tabAccess['delete'] === '1') {
            if (!is_array($fields = Tools14::getValue('fieldsBox'))) {
                $fields = array();
            }

            $object = new $this->className();
            if (!$object->addFieldsRequiredDatabase($fields)) {
                $this->_errors[] = $this->trans('Error in updating required fields', array(), 'Modules.Autoupgrade.Admin');
            } else {
                Tools14::redirectAdmin($currentIndex.'&conf=4&token='.$token);
            }
        }
    }

    protected function updateOptions($token)
    {
        global $currentIndex;

        if ($this->tabAccess['edit'] === '1') {
            foreach ($this->_fieldsOptions as $key => $field) {
                if ($this->validateField(Tools14::getValue($key), $field)) {
                    // check if a method updateOptionFieldName is available
                    $method_name = 'updateOption'.Tools14::toCamelCase($key, true);
                    if (method_exists($this, $method_name)) {
                        $this->$method_name(Tools14::getValue($key));
                    } elseif ($field['type'] == 'textLang' or $field['type'] == 'textareaLang') {
                        $languages = Language::getLanguages(false);
                        $list = array();
                        foreach ($languages as $language) {
                            $val = (isset($field['cast']) ? $field['cast'](Tools14::getValue($key.'_'.$language['id_lang'])) : Tools14::getValue($key.'_'.$language['id_lang']));
                            if (Validate::isCleanHtml($val)) {
                                $list[$language['id_lang']] = $val;
                            } else {
                                $this->_errors[] = $this->trans(
                                    'Can not add configuration %configurationkey% for lang %isocode%',
                                    array(
                                        '%configurationkey%' => $key,
                                        '%isocode%' => Language::getIsoById((int)$language['id_lang']),
                                    ),
                                    'Modules.Autoupgrade.Admin'
                                );
                            }
                        }
                        Configuration::updateValue($key, $list);
                    } else {
                        $val = (isset($field['cast']) ? $field['cast'](Tools14::getValue($key)) : Tools14::getValue($key));
                        if (Validate::isCleanHtml($val)) {
                            Configuration::updateValue($key, $val);
                        } else {
                            $this->_errors[] = $this->trans('Can not add configuration %configurationkey%', array('%configurationkey%' => $key), 'Modules.Autoupgrade.Admin');
                        }
                    }
                }
            }

            if (count($this->_errors) <= 0) {
                Tools14::redirectAdmin($currentIndex.'&conf=6&token='.$token);
            }
        } else {
            $this->_errors[] = $this->trans('You do not have permission to edit this.', array(), 'Admin.Notifications.Error');
        }
    }

    protected function validateField($value, $field)
    {
        if (isset($field['validation'])) {
            $validate = new Validate();
            if (method_exists($validate, $field['validation'])) {
                if (!Validate::$field['validation']($value)) {
                    $this->_errors[] = $this->trans('%title% : Incorrect value', array('%title%' => $field['title']), 'Modules.Autoupgrade.Admin');
                    return false;
                }
            }
        }

        return true;
    }

    protected function uploadImage($id, $name, $dir, $ext = false)
    {
        if (isset($_FILES[$name]['tmp_name']) and !empty($_FILES[$name]['tmp_name'])) {
            // Delete old image
            if (Validate::isLoadedObject($object = $this->loadObject())) {
                $object->deleteImage();
            } else {
                return false;
            }

            // Check image validity
            if ($error = checkImage($_FILES[$name], $this->maxImageSize)) {
                $this->_errors[] = $error;
            } elseif (!$tmpName = tempnam(_PS_TMP_IMG_DIR_, 'PS') or !move_uploaded_file($_FILES[$name]['tmp_name'], $tmpName)) {
                return false;
            } else {
                $_FILES[$name]['tmp_name'] = $tmpName;
                // Copy new image
                if (!imageResize($tmpName, _PS_IMG_DIR_.$dir.$id.'.'.$this->imageType, null, null, ($ext ? $ext : $this->imageType))) {
                    $this->_errors[] = $this->trans('An error occurred while uploading the image.', array(), 'Admin.Notifications.Error');
                }
                if (sizeof($this->_errors)) {
                    return false;
                }
                if ($this->afterImageUpload()) {
                    unlink($tmpName);
                    return true;
                }
                return false;
            }
        }
        return true;
    }



    protected function uploadIco($name, $dest)
    {
        if (isset($_FILES[$name]['tmp_name']) and !empty($_FILES[$name]['tmp_name'])) {
            /* Check ico validity */
            if ($error = checkIco($_FILES[$name], $this->maxImageSize)) {
                $this->_errors[] = $error;
            }

            /* Copy new ico */
            elseif (!copy($_FILES[$name]['tmp_name'], $dest)) {
                $this->_errors[] = $this->trans(
                    'An error occurred while uploading favicon: %iconname% to %destination%',
                    array(
                        '%iconname%' => $_FILES[$name]['tmp_name'],
                        '%destination%' => $dest,
                    ),
                    'Modules.Autoupgrade.Admin'
                );
            }
        }
        return !sizeof($this->_errors) ? true : false;
    }

    /**
     * Overload this method for custom checking
     *
     * @param integer $id Object id used for deleting images
     * @return boolean
     */
    protected function postImage($id)
    {
        if (isset($this->fieldImageSettings['name']) and isset($this->fieldImageSettings['dir'])) {
            return $this->uploadImage($id, $this->fieldImageSettings['name'], $this->fieldImageSettings['dir'].'/');
        } elseif (!empty($this->fieldImageSettings)) {
            foreach ($this->fieldImageSettings as $image) {
                if (isset($image['name']) and isset($image['dir'])) {
                    $this->uploadImage($id, $image['name'], $image['dir'].'/');
                }
            }
        }
        return !sizeof($this->_errors) ? true : false;
    }

    /**
     * Copy datas from $_POST to object
     *
     * @param object &$object Object
     * @param string $table Object table
     */
    protected function copyFromPost(&$object, $table)
    {
        /* Classical fields */
        foreach ($_POST as $key => $value) {
            if (key_exists($key, $object) and $key != 'id_'.$table) {
                /* Do not take care of password field if empty */
                if ($key == 'passwd' and Tools14::getValue('id_'.$table) and empty($value)) {
                    continue;
                }
                /* Automatically encrypt password in MD5 */
                if ($key == 'passwd' and !empty($value)) {
                    $value = Tools14::encrypt($value);
                }
                $object->{$key} = $value;
            }
        }

        /* Multilingual fields */
        $rules = call_user_func(array(get_class($object), 'getValidationRules'), get_class($object));
        if (sizeof($rules['validateLang'])) {
            $languages = Language::getLanguages(false);
            foreach ($languages as $language) {
                foreach (array_keys($rules['validateLang']) as $field) {
                    if (isset($_POST[$field.'_'.(int)($language['id_lang'])])) {
                        $object->{$field}[(int)($language['id_lang'])] = $_POST[$field.'_'.(int)($language['id_lang'])];
                    }
                }
            }
        }
    }

    /**
     * Display errors
     */
    public function displayErrors()
    {
        if ($nbErrors = count($this->_errors) and $this->_includeContainer) {
            echo '<script type="text/javascript">
				$(document).ready(function() {
					$(\'#hideError\').unbind(\'click\').click(function(){
						$(\'.error\').hide(\'slow\', function (){
							$(\'.error\').remove();
						});
						return false;
					});
				});
			  </script>
			<div class="error"><span style="float:right"><a id="hideError" href=""><img alt="X" src="../img/admin/close.png" /></a></span><img src="../img/admin/error2.png" />';
            if (count($this->_errors) == 1) {
                echo $this->_errors[0];
            } else {
                echo $nbErrors.' '.$this->trans('errors', array(), 'Modules.Autoupgrade.Admin').'<br /><ol>';
                foreach ($this->_errors as $error) {
                    echo '<li>'.$error.'</li>';
                }
                echo '</ol>';
            }
            echo '</div>';
        }
        $this->includeSubTab('displayErrors');
    }

    /**
     * Display a warning message
     *
     * @param string $warn Warning message to display
     */
    public function displayWarning($warn)
    {
        $str_output = '';
        if (!empty($warn)) {
            $str_output .= '<script type="text/javascript">
					$(document).ready(function() {
						$(\'#linkSeeMore\').unbind(\'click\').click(function(){
							$(\'#seeMore\').show(\'slow\');
							$(this).hide();
							$(\'#linkHide\').show();
							return false;
						});
						$(\'#linkHide\').unbind(\'click\').click(function(){
							$(\'#seeMore\').hide(\'slow\');
							$(this).hide();
							$(\'#linkSeeMore\').show();
							return false;
						});
						$(\'#hideWarn\').unbind(\'click\').click(function(){
							$(\'.warn\').hide(\'slow\', function (){
								$(\'.warn\').remove();
							});
							return false;
						});
					});
				  </script>
			<div class="warn">';
            if (!is_array($warn)) {
                if (file_exists(__PS_BASE_URI__.'img/admin/warn2.png')) {
                    $str_output .= '<img src="'.__PS_BASE_URI__.'img/admin/warn2.png" />';
                } else {
                    $str_output .= '<img src="'.__PS_BASE_URI__.'img/admin/warning.gif" />';
                }


                $str_output .= $warn;
            } else {
                $str_output .= '<span style="float:right"><a id="hideWarn" href=""><img alt="X" src="../img/admin/close.png" /></a></span><img src="../img/admin/warn2.png" />';
                if (count($warn) > 1) {
                    $str_output .= $this->trans('There are %count% warnings', array('%count%' => count($warn)), 'Modules.Autoupgrade.Admin');
                } else {
                    $str_output .= $this->trans('There is %count% warning', array('%count%' => count($warn)), 'Modules.Autoupgrade.Admin');
                }
                $str_output .= '<span style="margin-left:20px;" id="labelSeeMore">
				<a id="linkSeeMore" href="#" style="text-decoration:underline">'.$this->trans('Click here to see more', array(), 'Modules.Autoupgrade.Admin').'</a>
				<a id="linkHide" href="#" style="text-decoration:underline;display:none">'.$this->trans('Hide warning', array(), 'Modules.Autoupgrade.Admin').'</a></span><ul style="display:none;" id="seeMore">';
                foreach ($warn as $val) {
                    $str_output .= '<li>'.$val.'</li>';
                }
                $str_output .= '</ul>';
            }
            $str_output .= '</div>';
        }
        echo $str_output;
    }

    /**
     * Display confirmations
     */
    public function displayConf()
    {
        if ($conf = Tools14::getValue('conf')) {
            echo '
			<div class="alert alert-success">
				'.$this->_conf[(int)($conf)].'
			</div>';
        }
    }


    public function displayTop()
    {
    }

    protected function _displayEnableLink($token, $id, $value, $active,  $id_category = null, $id_product = null)
    {
        global $currentIndex;

        echo '<a href="'.$currentIndex.'&'.$this->identifier.'='.$id.'&'.$active.$this->table.
            ((int)$id_category and (int)$id_product ? '&id_category='.$id_category : '').'&token='.($token!=null ? $token : $this->token).'">
			<img src="../img/admin/'.($value ? 'enabled.gif' : 'disabled.gif').'"
			alt="'.($value ? $this->trans('Enabled', array(), 'Admin.Global') : $this->trans('Disabled', array(), 'Admin.Global')).'" title="'.($value ? $this->trans('Enabled', array(), 'Admin.Global') : $this->trans('Disabled', array(), 'Admin.Global')).'" /></a>';
    }

    protected function _displayDuplicate($token = null, $id)
    {
        global $currentIndex;

        $_cacheLang['Duplicate'] = $this->trans('Duplicate', array(), 'Admin.Actions');
        $_cacheLang['Copy images too?'] = $this->trans('This will copy the images too. If you wish to proceed, click "Yes". If not, click "No".', array(), 'Admin.Catalog.Notification');

        $duplicate = $currentIndex.'&'.$this->identifier.'='.$id.'&duplicate'.$this->table;

        echo '
			<a class="pointer" onclick="if (confirm(\''.$_cacheLang['Copy images too?'].'\')) document.location = \''.$duplicate.'&token='.($token!=null ? $token : $this->token).'\'; else document.location = \''.$duplicate.'&noimage=1&token='.($token ? $token : $this->token).'\';">
			<img src="../img/admin/duplicate.png" alt="'.$_cacheLang['Duplicate'].'" title="'.$_cacheLang['Duplicate'].'" /></a>';
    }

    protected function _displayViewLink($token = null, $id)
    {
        global $currentIndex;

        $_cacheLang['View'] = $this->trans('View', array(), 'Admin.Actions');

        echo '
			<a href="'.$currentIndex.'&'.$this->identifier.'='.$id.'&view'.$this->table.'&token='.($token!=null ? $token : $this->token).'">
			<img src="../img/admin/details.gif" alt="'.$_cacheLang['View'].'" title="'.$_cacheLang['View'].'" /></a>';
    }

    protected function _displayEditLink($token = null, $id)
    {
        global $currentIndex;

        $_cacheLang['Edit'] = $this->trans('Edit', array(), 'Admin.Actions');

        echo '
			<a href="'.$currentIndex.'&'.$this->identifier.'='.$id.'&update'.$this->table.'&token='.($token!=null ? $token : $this->token).'">
			<img src="../img/admin/edit.gif" alt="" title="'.$_cacheLang['Edit'].'" /></a>';
    }

    protected function _displayDeleteLink($token = null, $id)
    {
        global $currentIndex;

        $_cacheLang['Delete'] = $this->trans('Delete', array(), 'Admin.Actions');
        $_cacheLang['DeleteItem'] = $this->trans('Delete item #', array(), 'Modules.Autoupgrade.Admin');

        echo '
			<a href="'.$currentIndex.'&'.$this->identifier.'='.$id.'&delete'.$this->table.'&token='.($token!=null ? $token : $this->token).'" onclick="return confirm(\''.$_cacheLang['DeleteItem'].$id.' ?'.
                    (!is_null($this->specificConfirmDelete) ? '\r'.$this->specificConfirmDelete : '').'\');">
			<img src="../img/admin/delete.gif" alt="'.$_cacheLang['Delete'].'" title="'.$_cacheLang['Delete'].'" /></a>';
    }

    /**
     * Close list table and submit button
     */
    public function displayListFooter($token = null)
    {
        echo '</table>';
        if ($this->delete) {
            echo '<p><input type="submit" class="button" name="submitDel'.$this->table.'" value="'.$this->trans('Delete selection', array(), 'Admin.Actions').'" onclick="return confirm(\''.$this->trans('Delete selected items?', array(), 'Admin.Notifications.Warning').'\');" /></p>';
        }
        echo '
				</td>
			</tr>
		</table>
		<input type="hidden" name="token" value="'.($token ? $token : $this->token).'" />
		</form>';
        if (isset($this->_includeTab) and sizeof($this->_includeTab)) {
            echo '<br /><br />';
        }
    }

    /**
     * Options lists
     */
    public function displayOptionsList()
    {
        global $currentIndex, $cookie, $tab;

        if (!isset($this->_fieldsOptions) or !sizeof($this->_fieldsOptions)) {
            return false;
        }

        $defaultLanguage = (int)Configuration::get('PS_LANG_DEFAULT');
        $this->_languages = Language::getLanguages(false);
        $tab = Tab::getTab((int)$cookie->id_lang, Tab::getIdFromClassName($tab));
        echo '<br /><br />';
        echo(isset($this->optionTitle) ? '<h2>'.$this->optionTitle.'</h2>' : '');
        echo '
		<script type="text/javascript">
			id_language = Number('.$defaultLanguage.');
		</script>
		<form action="'.$currentIndex.'" id="'.$tab['name'].'" name="'.$tab['name'].'" method="post">
			<fieldset>';
        echo(isset($this->optionTitle) ? '<legend>
					<img src="'.(!empty($tab['module']) && file_exists($_SERVER['DOCUMENT_ROOT']._MODULE_DIR_.$tab['module'].'/'.$tab['class_name'].'.gif') ? _MODULE_DIR_.$tab['module'].'/' : '../img/t/').$tab['class_name'].'.gif" />'
                    .$this->optionTitle.'</legend>' : '');
        foreach ($this->_fieldsOptions as $key => $field) {
            $val = Tools14::getValue($key, Configuration::get($key));
            if ($field['type'] != 'textLang') {
                if (!Validate::isCleanHtml($val)) {
                    $val = Configuration::get($key);
                }
            }

            echo '<label>'.$field['title'].' </label>
			<div class="margin-form">';
            switch ($field['type']) {
                case 'select':
                    echo '<select name="'.$key.'">';
                    foreach ($field['list'] as $value) {
                        echo '<option
							value="'.(isset($field['cast']) ? $field['cast']($value[$field['identifier']]) : $value[$field['identifier']]).'"'.($val == $value[$field['identifier']] ? ' selected="selected"' : '').'>'.$value['name'].'</option>';
                    }
                    echo '</select>';
                    break;
                case 'bool':
                    echo '<label class="t" for="'.$key.'_on"><img src="../img/admin/enabled.gif" alt="'.$this->trans('Yes', array(), 'Admin.Global').'" title="'.$this->trans('Yes', array(), 'Admin.Global').'" /></label>
					<input type="radio" name="'.$key.'" id="'.$key.'_on" value="1"'.($val ? ' checked="checked"' : '').' />
					<label class="t" for="'.$key.'_on"> '.$this->trans('Yes', array(), 'Admin.Global').'</label>
					<label class="t" for="'.$key.'_off"><img src="../img/admin/disabled.gif" alt="'.$this->trans('No', array(), 'Admin.Global').'" title="'.$this->trans('No', array(), 'Admin.Global').'" style="margin-left: 10px;" /></label>
					<input type="radio" name="'.$key.'" id="'.$key.'_off" value="0" '.(!$val ? 'checked="checked"' : '').'/>
					<label class="t" for="'.$key.'_off"> '.$this->trans('No', array(), 'Admin.Global').'</label>';
                    break;
                case 'textLang':
                    foreach ($this->_languages as $language) {
                        $val = Tools14::getValue($key.'_'.$language['id_lang'], Configuration::get($key, $language['id_lang']));
                        if (!Validate::isCleanHtml($val)) {
                            $val = Configuration::get($key);
                        }
                        echo '
						<div id="'.$key.'_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
							<input size="'.$field['size'].'" type="text" name="'.$key.'_'.$language['id_lang'].'" value="'.$val.'" />
						</div>';
                    }
                    $this->displayFlags($this->_languages, $defaultLanguage, $key, $key);
                    echo '<br style="clear:both">';
                    break;
                case 'textareaLang':
                    foreach ($this->_languages as $language) {
                        $val = Configuration::get($key, $language['id_lang']);
                        echo '
						<div id="'.$key.'_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
							<textarea rows="'.(int)($field['rows']).'" cols="'.(int)($field['cols']).'"  name="'.$key.'_'.$language['id_lang'].'">'.str_replace('\r\n', "\n", $val).'</textarea>
						</div>';
                    }
                    $this->displayFlags($this->_languages, $defaultLanguage, $key, $key);
                    echo '<br style="clear:both">';
                    break;
                case 'text':
                default:
                    echo '<input type="text" name="'.$key.'" value="'.$val.'" size="'.$field['size'].'" />'.(isset($field['suffix']) ? $field['suffix'] : '');
            }

            if (isset($field['required']) and $field['required']) {
                echo ' <sup>*</sup>';
            }

            echo(isset($field['desc']) ? '<p>'.$field['desc'].'</p>' : '');
            echo '</div>';
        }
        echo '<div class="margin-form">
					<input type="submit" value="'.$this->trans('Save', array(), 'Admin.Actions').'" name="submitOptions'.$this->table.'" class="button" />
				</div>
			</fieldset>
			<input type="hidden" name="token" value="'.$this->token.'" />
		</form>';
    }

    /**
     * Load class object using identifier in $_GET (if possible)
     * otherwise return an empty object, or die
     *
     * @param boolean $opt Return an empty object if load fail
     * @return object
     */
    protected function loadObject($opt = false)
    {
        if ($id = (int)(Tools14::getValue($this->identifier)) and Validate::isUnsignedId($id)) {
            if (!$this->_object) {
                $this->_object = new $this->className($id);
            }
            if (Validate::isLoadedObject($this->_object)) {
                return $this->_object;
            }
            $this->_errors[] = $this->trans('Object cannot be loaded (not found)', array(), 'Modules.Autoupgrade.Admin');
        } elseif ($opt) {
            $this->_object = new $this->className();
            return $this->_object;
        } else {
            $this->_errors[] = $this->trans('Object cannot be loaded (identifier missing or invalid)', array(), 'Modules.Autoupgrade.Admin');
        }

        $this->displayErrors();
    }

    /**
     * Return field value if possible (both classical and multilingual fields)
     *
     * Case 1 : Return value if present in $_POST / $_GET
     * Case 2 : Return object value
     *
     * @param object $obj Object
     * @param string $key Field name
     * @param integer $id_lang Language id (optional)
     * @return string
     */
    protected function getFieldValue($obj, $key, $id_lang = null)
    {
        if ($id_lang) {
            $defaultValue = ($obj->id and isset($obj->{$key}[$id_lang])) ? $obj->{$key}[$id_lang] : '';
        } else {
            $defaultValue = isset($obj->{$key}) ? $obj->{$key} : '';
        }

        return Tools14::getValue($key.($id_lang ? '_'.$id_lang : ''), $defaultValue);
    }

    /**
     * Display form
     *
     * @global string $currentIndex Current URL in order to keep current Tab
     */
    public function displayForm($firstCall = true)
    {
        global $cookie;

        $allowEmployeeFormLang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        if ($allowEmployeeFormLang && !$cookie->employee_form_lang) {
            $cookie->employee_form_lang = (int)(Configuration::get('PS_LANG_DEFAULT'));
        }
        $useLangFromCookie = false;
        $this->_languages = Language::getLanguages(false);
        if ($allowEmployeeFormLang) {
            foreach ($this->_languages as $lang) {
                if ($cookie->employee_form_lang == $lang['id_lang']) {
                    $useLangFromCookie = true;
                }
            }
        }
        if (!$useLangFromCookie) {
            $this->_defaultFormLanguage = (int)(Configuration::get('PS_LANG_DEFAULT'));
        } else {
            $this->_defaultFormLanguage = (int)($cookie->employee_form_lang);
        }

        // Only if it is the first call to displayForm, otherwise it has already been defined
        if ($firstCall) {
            echo '
			<script type="text/javascript">
				$(document).ready(function() {
					id_language = '.$this->_defaultFormLanguage.';
					languages = new Array();';
            foreach ($this->_languages as $k => $language) {
                echo '
					languages['.$k.'] = {
						id_lang: '.(int)$language['id_lang'].',
						iso_code: \''.$language['iso_code'].'\',
						name: \''.htmlentities($language['name'], ENT_COMPAT, 'UTF-8').'\'
					};';
            }
            echo '
					displayFlags(languages, id_language, '.$allowEmployeeFormLang.');
				});
			</script>';
        }
    }

    /**
     * Display object details
     *
     * @global string $currentIndex Current URL in order to keep current Tab
     */
    public function viewDetails()
    {
        global $currentIndex;
    }

    /**
     * Called before deletion
     *
     * @param object $object Object
     * @return boolean
     */
    protected function beforeDelete($object)
    {
        return true;
    }

    /**
     * Called before deletion
     *
     * @param object $object Object
     * @return boolean
     */
    protected function afterDelete($object, $oldId)
    {
        return true;
    }

    protected function afterAdd($object)
    {
        return true;
    }

    protected function afterUpdate($object)
    {
        return true;
    }

    /**
     * Check rights to view the current tab
     *
     * @return boolean
     */

    protected function afterImageUpload()
    {
        return true;
    }

    /**
     * Check rights to view the current tab
     *
     * @return boolean
     */

    public function viewAccess($disable = false)
    {
        global $cookie;

        if ($disable) {
            return true;
        }

        $this->tabAccess = Profile::getProfileAccess($cookie->profile, $this->id);

        if ($this->tabAccess['view'] === '1') {
            return true;
        }
        return false;
    }

    /**
     * Check for security token
     */
    public function checkToken()
    {
        $token = Tools14::getValue('token');
        return (!empty($token) and $token === $this->token);
    }

    /**
      * Display flags in forms for translations
      *
      * @param array $languages All languages available
      * @param integer $defaultLanguage Default language id
      * @param string $ids Multilingual div ids in form
      * @param string $id Current div id]
      * #param boolean $return define the return way : false for a display, true for a return
      */
    public function displayFlags($languages, $defaultLanguage, $ids, $id, $return = false)
    {
        if (sizeof($languages) == 1) {
            return false;
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

        if ($return) {
            return $output;
        }
        echo $output;
    }

    protected function filterToField($key, $filter)
    {
        foreach ($this->fieldsDisplay as $field) {
            if (array_key_exists('filter_key', $field) and $field['filter_key'] == $key) {
                return $field;
            }
        }
        if (array_key_exists($filter, $this->fieldsDisplay)) {
            return $this->fieldsDisplay[$filter];
        }
        return false;
    }

    protected function warnDomainName()
    {
        if ($_SERVER['HTTP_HOST'] != Configuration::get('PS_SHOP_DOMAIN') and $_SERVER['HTTP_HOST'] != Configuration::get('PS_SHOP_DOMAIN_SSL')) {
            $this->displayWarning($this->trans('Your are currently connected with the following domain name:', array(), 'Modules.Autoupgrade.Admin').' <span style="color: #CC0000;">'.$_SERVER['HTTP_HOST'].'</span><br />'.
            $this->trans('This one is different from the main shop domain name set in "Preferences > SEO & URLs":', array(), 'Modules.Autoupgrade.Admin').' <span style="color: #CC0000;">'.Configuration::get('PS_SHOP_DOMAIN').'</span><br />
			<a href="index.php?tab=AdminMeta&token='.Tools14::getAdminTokenLite('AdminMeta').'#SEO%20%26%20URLs">'.
            $this->trans('Click here if you want to modify the main shop domain name', array(), 'Modules.Autoupgrade.Admin').'</a>');
        }
    }
    /*
    * from 1.4 AdminPreferences
    */
    protected function _postConfig($fields)
    {
        global $currentIndex, $smarty;

        $languages = Language::getLanguages(false);
        if (method_exists('Tools', 'clearCache')) {
            Tools14::clearCache($smarty);
        }

        /* Check required fields */
        foreach ($fields as $field => $values) {
            if (isset($values['required']) and $values['required']) {
                if (isset($values['type']) and $values['type'] == 'textLang') {
                    foreach ($languages as $language) {
                        if (($value = Tools14::getValue($field.'_'.$language['id_lang'])) == false and (string)$value != '0') {
                            $this->_errors[] = $this->trans(
                                'Field %name% is required',
                                array(
                                    '%name%' => '<b>'.$values['title'].'</b>'
                                ),
                                'Modules.Autoupgrade.Admin'
                            );
                        }
                    }
                } elseif (($value = Tools14::getValue($field)) == false and (string)$value != '0') {
                    $this->_errors[] = $this->trans(
                        'Field %name% is required',
                        array(
                            '%name%' => '<b>'.$values['title'].'</b>'
                        ),
                        'Modules.Autoupgrade.Admin'
                    );
                }
            }
        }

        /* Check fields validity */
        foreach ($fields as $field => $values) {
            if (isset($values['type']) and $values['type'] == 'textLang') {
                foreach ($languages as $language) {
                    if (Tools14::getValue($field.'_'.$language['id_lang']) and isset($values['validation'])) {
                        if (!Validate::$values['validation'](Tools14::getValue($field.'_'.$language['id_lang']))) {
                            $this->_errors[] = $this->trans(
                                'Field %name% is invalid',
                                array(
                                    '%name%' => '<b>'.$values['title'].'</b>'
                                ),
                                'Modules.Autoupgrade.Admin'
                            );
                        }
                    }
                }
            } elseif (Tools14::getValue($field) and isset($values['validation'])) {
                if (!Validate::$values['validation'](Tools14::getValue($field))) {
                    $this->_errors[] = $this->trans(
                        'Field %name% is invalid',
                        array(
                            '%name%' => '<b>'.$values['title'].'</b>'
                        ),
                        'Modules.Autoupgrade.Admin'
                    );
                }
            }
        }

        /* Default value if null */
        foreach ($fields as $field => $values) {
            if (!Tools14::getValue($field) and isset($values['default'])) {
                $_POST[$field] = $values['default'];
            }
        }

        /* Save process */
        if (!sizeof($this->_errors)) {
            if (Tools14::isSubmit('submitAppearanceconfiguration')) {
                if (isset($_FILES['PS_LOGO']['tmp_name']) and $_FILES['PS_LOGO']['tmp_name']) {
                    if ($error = checkImage($_FILES['PS_LOGO'], 300000)) {
                        $this->_errors[] = $error;
                    }
                    if (!$tmpName = tempnam(_PS_TMP_IMG_DIR_, 'PS') or !move_uploaded_file($_FILES['PS_LOGO']['tmp_name'], $tmpName)) {
                        return false;
                    } elseif (!@imageResize($tmpName, _PS_IMG_DIR_.'logo.jpg')) {
                        $this->_errors[] = 'an error occurred during logo copy';
                    }
                    unlink($tmpName);
                }
                if (isset($_FILES['PS_LOGO_MAIL']['tmp_name']) and $_FILES['PS_LOGO_MAIL']['tmp_name']) {
                    if ($error = checkImage($_FILES['PS_LOGO_MAIL'], 300000)) {
                        $this->_errors[] = $error;
                    }
                    if (!$tmpName = tempnam(_PS_TMP_IMG_DIR_, 'PS_MAIL') or !move_uploaded_file($_FILES['PS_LOGO_MAIL']['tmp_name'], $tmpName)) {
                        return false;
                    } elseif (!@imageResize($tmpName, _PS_IMG_DIR_.'logo_mail.jpg')) {
                        $this->_errors[] = 'an error occurred during logo copy';
                    }
                    unlink($tmpName);
                }
                if (isset($_FILES['PS_LOGO_INVOICE']['tmp_name']) and $_FILES['PS_LOGO_INVOICE']['tmp_name']) {
                    if ($error = checkImage($_FILES['PS_LOGO_INVOICE'], 300000)) {
                        $this->_errors[] = $error;
                    }
                    if (!$tmpName = tempnam(_PS_TMP_IMG_DIR_, 'PS_INVOICE') or !move_uploaded_file($_FILES['PS_LOGO_INVOICE']['tmp_name'], $tmpName)) {
                        return false;
                    } elseif (!@imageResize($tmpName, _PS_IMG_DIR_.'logo_invoice.jpg')) {
                        $this->_errors[] = 'an error occurred during logo copy';
                    }
                    unlink($tmpName);
                }
                if (isset($_FILES['PS_STORES_ICON']['tmp_name']) and $_FILES['PS_STORES_ICON']['tmp_name']) {
                    if ($error = checkImage($_FILES['PS_STORES_ICON'], 300000)) {
                        $this->_errors[] = $error;
                    }
                    if (!$tmpName = tempnam(_PS_TMP_IMG_DIR_, 'PS_STORES_ICON') or !move_uploaded_file($_FILES['PS_STORES_ICON']['tmp_name'], $tmpName)) {
                        return false;
                    } elseif (!@imageResize($tmpName, _PS_IMG_DIR_.'logo_stores.gif')) {
                        $this->_errors[] = 'an error occurred during logo copy';
                    }
                    unlink($tmpName);
                }
                $this->uploadIco('PS_FAVICON', _PS_IMG_DIR_.'favicon.ico');
            }

            /* Update settings in database */
            if (!sizeof($this->_errors)) {
                foreach ($fields as $field => $values) {
                    unset($val);
                    if (isset($values['type']) and $values['type'] == 'textLang') {
                        foreach ($languages as $language) {
                            $val[$language['id_lang']] = isset($values['cast']) ? $values['cast'](Tools14::getValue($field.'_'.$language['id_lang'])) : Tools14::getValue($field.'_'.$language['id_lang']);
                        }
                    } else {
                        $val = isset($values['cast']) ? $values['cast'](Tools14::getValue($field)) : Tools14::getValue($field);
                    }

                    Configuration::updateValue($field, $val);
                }
                Tools14::redirectAdmin($currentIndex.'&conf=6'.'&token='.$this->token);
            }
        }
    }

    /* Dirty fix bug PNM-2673 */
    public function addCSS($tmp)
    {
        return $tmp;
    }
}
