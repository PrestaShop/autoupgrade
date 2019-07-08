# 1-Click Upgrade

## About

Provides an automated method to upgrade your shop to the latest version of PrestaShop.
This module is compatible with all PrestaShop 1.6 & 1.7.

# Prerequisites

* PrestaShop 1.6 or 1.7
* PHP 5.6+ 

For older PHP versions, see previous releases of the module [(ex. v1.6.8)](https://github.com/PrestaShop/autoupgrade/releases/tag/v1.6.8).
Note they are unsupported and we strongly recommend you to upgrade your PHP version.

# Installation

All versions can be found in the [releases list](https://github.com/PrestaShop/autoupgrade/releases).

## Create a module from source code
* Clone (`git clone https://github.com/PrestaShop/autoupgrade.git`) or [download](https://github.com/PrestaShop/autoupgrade/archive/master.zip) the source code. You can also download a release **Source code** ([ex. v4.4.1](https://github.com/PrestaShop/autoupgrade/archive/v4.4.1.zip)). If you download a source code archive, you need extract the file and rename the extracted folder to **autoupgrade**
* Enter into folder **autoupgrade** and run the command `composer install`  ([composer](https://getcomposer.org/)).
* Create a new zip file of **autoupgrade** folder
* Now you can upload into your module pages

# Running an upgrade on PrestaShop

Upgrading a shop can be done via:

* the configuration page of the module (access from your BO module page)
* in command line by calling the file *cli-upgrade.php*

## Command line parameters

Upgrade can be automated by calling *cli-upgrade.php*.
The following parameters are mandatory:

* **--dir**: Tells where the admin directory is.
* **--channel**: Selects what upgrade to run (minor, major etc.)
* **--action**: Advanced users only. Sets the step you want to start from (Default: `UpgradeNow`, [other values available](classes/TaskRunner/Upgrade/)).

```
$ php cli-upgrade.php --dir=admin-dev --channel=major
```

# Rollback a shop

If an error occurs during the upgrade process, the rollback will be suggested.
In case you lost the page from your backoffice, note it can be triggered via CLI.

## Command line parameters

Rollback can be automated by calling *cli-rollback.php*.
The following parameters are mandatory:

* **--dir**: Tells where the admin directory is.
* **--backup**: Select the backup to restore (this can be found in your folder `<admin>/autoupgrade/backup/`)

```
$ php cli-rollback.php  --dir=admin-dev --backup=V1.7.5.1_20190502-191341-22e883bd
```

## Contributing

PrestaShop modules are open-source extensions to the PrestaShop e-commerce solution. Everyone is welcome and even encouraged to contribute with their own improvements.

To contribute on this project, start by cloning the repository.
You must have [composer][4] installed on your computer. Run the following command:

```
$ composer install
```

Your module will be available with development libraries.

### GitHub Requirements

Contributors **must** follow the following rules:

* **Make your Pull Request on the "dev" branch**, NOT the "master" branch.
* Do not update the module's version number.
* Follow [the coding standards][1].

### Process in details

Contributors wishing to edit a module's files should follow the following process:

1. Create your GitHub account, if you do not have one already.
2. Fork the autoupgrade project to your GitHub account.
3. Clone your fork to your local machine in the ```/modules``` directory of your PrestaShop installation.
4. Create a branch in your local clone of the module for your changes.
5. Change the files in your branch. Be sure to follow [the coding standards][1]!
6. Push your changed branch to your fork in your GitHub account.
7. Create a pull request for your changes **on the _'dev'_ branch** of the module's project. Be sure to follow [the commit message norm][2] in your pull request. If you need help to make a pull request, read the [Github help page about creating pull requests][3].
8. Wait for one of the core developers either to include your change in the codebase, or to comment on possible improvements you should make to your code.

That's it: you have contributed to this open-source project! Congratulations!

[1]: http://doc.prestashop.com/display/PS16/Coding+Standards
[2]: http://doc.prestashop.com/display/PS16/How+to+write+a+commit+message
[3]: https://help.github.com/articles/using-pull-requests
[4]: https://getcomposer.org/download/
