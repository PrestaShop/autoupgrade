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
    public static function getJsErrorMsgs($install_version)
    {
        $ret = '
var txtError = new Array();
txtError[0] = "'.addslashes(self::trans('Required field', array(), 'Modules.Autoupgrade.Admin')).'";
txtError[1] = "'.addslashes(self::trans('Too long!', array(), 'Modules.Autoupgrade.Admin')).'";
txtError[2] = "'.addslashes(self::trans('Fields are different!', array(), 'Modules.Autoupgrade.Admin')).'";
txtError[3] = "'.addslashes(self::trans('This email address is wrong!', array(), 'Modules.Autoupgrade.Admin')).'";
txtError[4] = "'.addslashes(self::trans('Impossible to send the email!', array(), 'Modules.Autoupgrade.Admin')).'";
txtError[5] = "'.addslashes(self::trans('Cannot create settings file, if /app/config/parameters.php exists, please give the public write permissions to this file, else please create a file named parameters.php in config directory.', array(), 'Modules.Autoupgrade.Admin')).'";
txtError[6] = "'.addslashes(self::trans('Cannot write settings file, please create a file named settings.inc.php in the "config" directory.', array(), 'Modules.Autoupgrade.Admin')).'";
txtError[7] = "'.addslashes(self::trans('Impossible to upload the file!', array(), 'Modules.Autoupgrade.Admin')).'";
txtError[8] = "'.addslashes(self::trans('Data integrity is not valided. Hack attempt?', array(), 'Modules.Autoupgrade.Admin')).'";
txtError[9] = "'.addslashes(self::trans('Impossible to read the content of a MySQL content file.', array(), 'Modules.Autoupgrade.Admin')).'";
txtError[10] = "'.addslashes(self::trans('Cannot access a MySQL content file.', array(), 'Modules.Autoupgrade.Admin')).'";
txtError[11] = "'.addslashes(self::trans('Error while inserting data in the database:', array(), 'Modules.Autoupgrade.Admin')).'";
txtError[12] = "'.addslashes(self::trans('The password is incorrect (must be alphanumeric string with at least 8 characters)', array(), 'Install')).'";
txtError[14] = "'.addslashes(self::trans('At least one table with same prefix was already found, please change your prefix or drop your database', array(), 'Install')).'";
txtError[15] = "'.addslashes(self::trans('This is not a valid file name.', array(), 'Modules.Autoupgrade.Admin')).'";
txtError[16] = "'.addslashes(self::trans('This is not a valid image file.', array(), 'Modules.Autoupgrade.Admin')).'";
txtError[17] = "'.addslashes(self::trans('Error while creating the /app/config/parameters.php file.', array(), 'Modules.Autoupgrade.Admin')).'";
txtError[18] = "'.addslashes(self::trans('Error:', array(), 'Modules.Autoupgrade.Admin')).'";
txtError[19] = "'.addslashes(self::trans('This PrestaShop database already exists. Please revalidate your authentication information to the database.', array(), 'Modules.Autoupgrade.Admin')).'";
txtError[22] = "'.addslashes(self::trans('An error occurred while resizing the picture.', array(), 'Modules.Autoupgrade.Admin')).'";
txtError[23] = "'.addslashes(self::trans('Database connection is available!', array(), 'Modules.Autoupgrade.Admin')).'";
txtError[24] = "'.addslashes(self::trans('Database Server is available but database is not found', array(), 'Modules.Autoupgrade.Admin')).'";
txtError[25] = "'.addslashes(self::trans('Database Server is not found. Please verify the login, password and server fields.', array(), 'Modules.Autoupgrade.Admin')).'";
txtError[26] = "'.addslashes(self::trans('An error occurred while sending email, please verify your parameters.', array(), 'Modules.Autoupgrade.Admin')).'";
txtError[37] = "'.addslashes(self::trans('Impossible to write the image /img/logo.jpg. If this image already exists, please delete it.', array(), 'Modules.Autoupgrade.Admin')).'";
txtError[38] = "'.addslashes(self::trans('The uploaded file exceeds the upload_max_filesize directive in php.ini', array(), 'Modules.Autoupgrade.Admin')).'";
txtError[39] = "'.addslashes(self::trans('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form', array(), 'Modules.Autoupgrade.Admin')).'";
txtError[40] = "'.addslashes(self::trans('The uploaded file was only partially uploaded', array(), 'Modules.Autoupgrade.Admin')).'";
txtError[41] = "'.addslashes(self::trans('No file was uploaded.', array(), 'Modules.Autoupgrade.Admin')).'";
txtError[42] = "'.addslashes(self::trans('Missing a temporary folder', array(), 'Modules.Autoupgrade.Admin')).'";
txtError[43] = "'.addslashes(self::trans('Failed to write file to disk', array(), 'Modules.Autoupgrade.Admin')).'";
txtError[44] = "'.addslashes(self::trans('File upload stopped by extension', array(), 'Modules.Autoupgrade.Admin')).'";
txtError[45] = "'.addslashes(self::trans('Cannot convert your database\'s data to utf-8.', array(), 'Modules.Autoupgrade.Admin')).'";
txtError[47] = "'.addslashes(self::trans('Your firstname contains some invalid characters', array(), 'Install')).'";
txtError[46] = "'.addslashes(self::trans('Invalid shop name', array(), 'Install')).'";
txtError[49] = "'.addslashes(self::trans('Your database server does not support the utf-8 charset.', array(), 'Install')).'";
txtError[50] = "'.addslashes(self::trans('Your MySQL server does not support this engine, please use another one like MyISAM', array(), 'Modules.Autoupgrade.Admin')).'";
txtError[48] = "'.addslashes(self::trans('Your lastname contains some invalid characters', array(), 'Modules.Autoupgrade.Admin')).'";
txtError[51] = "'.addslashes(self::trans('The file /img/logo.jpg is not writable, please CHMOD 755 this file or CHMOD 777', array(), 'Modules.Autoupgrade.Admin')).'";
txtError[52] = "'.addslashes(self::trans('Invalid catalog mode', array(), 'Modules.Autoupgrade.Admin')).'";
txtError[999] = "'.addslashes(self::trans('No error code available', array(), 'Modules.Autoupgrade.Admin')).'";
//upgrader
txtError[27] = "'.addslashes(self::trans('This installer is too old.', array(), 'Modules.Autoupgrade.Admin')).'";
txtError[28] = "'.addslashes(self::trans('You already have the %s version.', array($install_version), 'Modules.Autoupgrade.Admin')).'";
txtError[29] = "'.addslashes(self::trans('There is no older version. Did you delete or rename the app/config/parameters.php file?', array(), 'Modules.Autoupgrade.Admin')).'";
txtError[30] = "'.addslashes(self::trans('The app/config/parameters.php file was not found. Did you delete or rename this file?', array(), 'Modules.Autoupgrade.Admin')).'";
txtError[31] = "'.addslashes(self::trans('Cannot find the SQL upgrade files. Please verify that the /install/upgrade/sql folder is not empty.', array(), 'Modules.Autoupgrade.Admin')).'";
txtError[32] = "'.addslashes(self::trans('No upgrade is possible.', array(), 'Modules.Autoupgrade.Admin')).'";
txtError[33] = "'.addslashes(self::trans('Error while loading SQL upgrade file.', array(), 'Modules.Autoupgrade.Admin')).'";
txtError[34] = "'.addslashes(self::trans('Error while inserting content into the database', array(), 'Modules.Autoupgrade.Admin')).'";
txtError[35] = "'.addslashes(self::trans('Unfortunately,', array(), 'Modules.Autoupgrade.Admin')).'";
txtError[36] = "'.addslashes(self::trans('SQL errors have occurred.', array(), 'Modules.Autoupgrade.Admin')).'";
txtError[37] = "'.addslashes(self::trans('The config/defines.inc.php file was not found. Where did you move it?', array(), 'Modules.Autoupgrade.Admin')).'";';
        return $ret;
    }

    public static function translatedString()
    {
        return 'var translated = new Array();
translated[0] = "'.addslashes(self::trans('Delete', array(), 'Admin.Actions')).'";
translated[1] = "'.addslashes(self::trans('Javascript error (parseJSON) detected for action ', array(), 'Modules.Autoupgrade.Admin')).'";
translated[2] = "'.addslashes(self::trans('Starting restoration...', array(), 'Modules.Autoupgrade.Admin')).'";
translated[3] = "'.addslashes(self::trans('Are you sure you want to delete this backup?', array(), 'Modules.Autoupgrade.Admin')).'";
translated[4] = "'.addslashes(self::trans('Your server cannot download the file. Please upload it first by ftp in your admin/autoupgrade directory', array(), 'Modules.Autoupgrade.Admin')).'";
translated[5] = "'.addslashes(self::trans('Click to refresh the page and use the new configuration', array(), 'Modules.Autoupgrade.Admin')).'";
translated[6] = "'.addslashes(self::trans('An update is currently in progress... Click "OK" to abort.', array(), 'Modules.Autoupgrade.Admin')).'";
translated[7] = "'.addslashes(self::trans('Upgrading PrestaShop ...', array(), 'Modules.Autoupgrade.Admin')).'";
translated[8] = "'.addslashes(self::trans('Upgrade complete', array(), 'Modules.Autoupgrade.Admin')).'";
translated[9] = "'.addslashes(self::trans('Upgrade Complete!', array(), 'Modules.Autoupgrade.Admin')).'";
translated[10] = "'.addslashes(self::trans('Upgrade complete, but warning notifications has been found.', array(), 'Modules.Autoupgrade.Admin')).'";
translated[11] = "'.addslashes(self::trans('Cookies have changed, you will need to log in again once you refreshed the page', array(), 'Modules.Autoupgrade.Admin')).'";
translated[12] = "'.addslashes(self::trans('Javascript and CSS files have changed, please clear your browser cache with CTRL-F5', array(), 'Modules.Autoupgrade.Admin')).'";
translated[13] = "'.addslashes(self::trans('Please check that your front-office theme is functional (try to create an account, place an order...)', array(), 'Modules.Autoupgrade.Admin')).'";
translated[14] = "'.addslashes(self::trans('Product images do not appear in the front-office? Try regenerating the thumbnails in Preferences > Images', array(), 'Modules.Autoupgrade.Admin')).'";
translated[15] = "'.addslashes(self::trans('Do not forget to reactivate your shop once you have checked everything!', array(), 'Modules.Autoupgrade.Admin')).'";
translated[16] = "'.addslashes(self::trans('ToDo list:', array(), 'Modules.Autoupgrade.Admin')).'";
translated[17] = "'.addslashes(self::trans('Restoration complete.', array(), 'Modules.Autoupgrade.Admin')).'";
translated[18] = "'.addslashes(self::trans('Error detected during', array(), 'Modules.Autoupgrade.Admin')).'";
translated[19] = "'.addslashes(self::trans('The request exceeded the max_time_limit. Please change your server configuration.', array(), 'Modules.Autoupgrade.Admin')).'";
translated[20] = "'.addslashes(self::trans('Manually go to %s button', array(), 'Modules.Autoupgrade.Admin')).'";
translated[21] = "'.addslashes(self::trans('End of process', array(), 'Modules.Autoupgrade.Admin')).'";
translated[22] = "'.addslashes(self::trans('Operation canceled. Checking for restoration...', array(), 'Modules.Autoupgrade.Admin')).'";
translated[23] = "'.addslashes(self::trans('Do you want to restore %backupname%?', array(), 'Modules.Autoupgrade.Admin')).'";
translated[24] = "'.addslashes(self::trans('Operation canceled. An error happened.', array(), 'Modules.Autoupgrade.Admin')).'";
translated[25] = "'.addslashes(self::trans('See or hide the list', array(), 'Modules.Autoupgrade.Admin')).'";
translated[26] = "'.addslashes(self::trans('Core file(s)', array(), 'Modules.Autoupgrade.Admin')).'";
translated[27] = "'.addslashes(self::trans('Mail file(s)', array(), 'Modules.Autoupgrade.Admin')).'";
translated[28] = "'.addslashes(self::trans('Translation file(s)', array(), 'Modules.Autoupgrade.Admin')).'";
translated[29] = "'.addslashes(self::trans('Your server cannot download the file. Please upload it to your FTP server, and put it in your /[admin]/autoupgrade directory.', array(), 'Modules.Autoupgrade.Admin')).'";
translated[30] = "'.addslashes(self::trans('See or hide the list', array(), 'Modules.Autoupgrade.Admin')).'";
translated[31] = "'.addslashes(self::trans('Theses files will be deleted', array(), 'Modules.Autoupgrade.Admin')).'";
translated[32] = "'.addslashes(self::trans('Theses files will be modified', array(), 'Modules.Autoupgrade.Admin')).'";
translated[33] = "'.addslashes(self::trans('Your server cannot download the file. Please upload it first by ftp in your admin/autoupgrade directory', array(), 'Modules.Autoupgrade.Admin')).'";
translated[34] = "'.addslashes(self::trans('Less options', array(), 'Modules.Autoupgrade.Admin')).'";
translated[35] = "'.addslashes(self::trans('More options (Expert mode)', array(), 'Modules.Autoupgrade.Admin')).'";
translated[36] = "'.addslashes(self::trans('Link and MD5 hash cannot be empty', array(), 'Modules.Autoupgrade.Admin')).'";
translated[37] = "'.addslashes(self::trans('You need to enter the version number associated with the archive.', array(), 'Modules.Autoupgrade.Admin')).'";
translated[38] = "'.addslashes(self::trans('No archive has been selected.', array(), 'Modules.Autoupgrade.Admin')).'";
translated[39] = "'.addslashes(self::trans('You need to enter the version number associated with the directory.', array(), 'Modules.Autoupgrade.Admin')).'";
translated[40] = "'.addslashes(self::trans('Please confirm that you want to skip the backup.', array(), 'Modules.Autoupgrade.Admin')).'";
translated[41] = "'.addslashes(self::trans('Please confirm that you want to preserve file options.', array(), 'Modules.Autoupgrade.Admin')).'";
';
    }

    public static function getJsInitValues($manualMode, $adminDir, $defaultAjaxResult, $backup, $token, $channel, $currentIndex)
    {
        $js = '';
        if ($manualMode) {
            $js .= 'var manualMode = true;'."\n";
        } else {
            $js .= 'var manualMode = false;'."\n";
        }

        // _PS_MODE_DEV_ will be available in js
        if (defined('_PS_MODE_DEV_') and _PS_MODE_DEV_) {
            $js .= 'var _PS_MODE_DEV_ = true;'."\n";
        }

        if ($backup) {
            $js .= 'var PS_AUTOUP_BACKUP = true;'."\n";
        }

        $js .= '
            var firstTimeParams = '.$defaultAjaxResult.';
            firstTimeParams = firstTimeParams.nextParams;
            firstTimeParams.firstTime = "1";';

        $js .= 'var token = "'. $token .'";';
        $js .= 'var currentIndex = "'. $currentIndex .'";';
        $js .= 'var adminDir = "'. $adminDir .'";';
        $js .= 'var adminUrl = "'. __PS_BASE_URI__.$adminDir .'";';

        $js .= 'var defaultMode = '. ($channel == 'major' ? 'switch_to_normal' : 'switch_to_advanced').';';
        return $js;
    }

    public function trans($string, $params = array(), $domain = null)
    {
        return $string;
    }
}