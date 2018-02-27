<?php
/*
 * 2007-2018 PrestaShop
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
 *  @copyright  2007-2018 PrestaShop SA
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\Module\AutoUpgrade\Parameters;

/**
 * File names where upgrade temporary content is stored
 */
class UpgradeFileNames
{
    /**
     * configFilename contains all configuration specific to the autoupgrade module
     *
     * @var string
     * @access public
     */
    const configFilename = 'config.var';

    /**
     * during upgradeFiles process,
     * this files contains the list of queries left to upgrade in a serialized array.
     * (this file is deleted in init() method if you reload the page)
     * @var string
     */
    const toUpgradeQueriesList = 'queriesToUpgrade.list';

    /**
     * during upgradeFiles process,
     * this files contains the list of files left to upgrade in a serialized array.
     * (this file is deleted in init() method if you reload the page)
     * @var string
     */
    const toUpgradeFileList = 'filesToUpgrade.list';

    /**
     * during upgradeModules process,
     * this files contains the list of modules left to upgrade in a serialized array.
     * (this file is deleted in init() method if you reload the page)
     * @var string
     */
    const toUpgradeModuleList = 'modulesToUpgrade.list';

    /**
     * during upgradeFiles process,
     * this files contains the list of files left to upgrade in a serialized array.
     * (this file is deleted in init() method if you reload the page)
     * @var string
     */
    const diffFileList = 'filesDiff.list';

    /**
     * during backupFiles process,
     * this files contains the list of files left to save in a serialized array.
     * (this file is deleted in init() method if you reload the page)
     * @var string
     */
    const toBackupFileList = 'filesToBackup.list';

    /**
     * during backupDb process,
     * this files contains the list of tables left to save in a serialized array.
     * (this file is deleted in init() method if you reload the page)
     * @var string
     */
    const toBackupDbList = 'tablesToBackup.list';

    /**
     * during restoreDb process,
     * this file contains a serialized array of queries which left to execute for restoring database
     * (this file is deleted in init() method if you reload the page)
     * @var string
     */
    const toRestoreQueryList = 'queryToRestore.list';
    const toCleanTable = 'tableToClean.list';

    /**
     * during restoreFiles process,
     * this file contains difference between queryToRestore and queries present in a backupFiles archive
     * (this file is deleted in init() method if you reload the page)
     * @var string
     */
    const toRemoveFileList = 'filesToRemove.list';

    /**
     * during restoreFiles process,
     * contains list of files present in backupFiles archive
     *
     * @var string
     */
    const fromArchiveFileList = 'filesFromArchive.list';

    /**
     * mailCustomList contains list of mails files which are customized,
     * relative to original files for the current PrestaShop version
     *
     * @var string
     */
    const mailCustomList = 'mails-custom.list';

    /**
     * tradCustomList contains list of mails files which are customized,
     * relative to original files for the current PrestaShop version
     *
     * @var string
     */
    const tradCustomList = 'translations-custom.list';

    /**
     * tmp_files contains an array of filename which will be removed
     * at the beginning of the upgrade process
     *
     * @var array
     */
    public static $tmp_files = array(
        'toUpgradeFileList',
        'toUpgradeQueriesList', // used ?
        'diffFileList',
        'toBackupFileList',
        'toBackupDbList',
        'toRestoreQueryList',
        'toCleanTable',
        'toRemoveFileList',
        'fromArchiveFileList',
        'mailCustomList',
        'tradCustomList',
    );

}