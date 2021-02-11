# 1-Click Upgrade

![PHP tests](https://github.com/PrestaShop/autoupgrade/workflows/PHP%20tests/badge.svg)
![Upgrades](https://github.com/PrestaShop/autoupgrade/workflows/Upgrades/badge.svg)
[![Latest Stable Version](https://poser.pugx.org/PrestaShop/autoupgrade/v)](//packagist.org/packages/PrestaShop/autoupgrade)
[![Total Downloads](https://poser.pugx.org/PrestaShop/autoupgrade/downloads)](//packagist.org/packages/PrestaShop/autoupgrade)
[![GitHub license](https://img.shields.io/github/license/PrestaShop/autoupgrade)](https://github.com/PrestaShop/autoupgrade/LICENSE.md)

## About

Upgrade to the latest version of PrestaShop in a few clicks, thanks to this automated method.
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

# Documentation

Documentation is hosted on [devdocs.prestashop.com][doc].

# Contributing

PrestaShop modules are open source extensions to the [PrestaShop e-commerce platform][prestashop]. Everyone is welcome and even encouraged to contribute with their own improvements!

Just make sure to follow our [contribution guidelines][contribution-guidelines].

## Reporting issues

You can report issues with this module in the main PrestaShop repository. [Click here to report an issue][report-issue].

# License

This module is released under the [Academic Free License 3.0][AFL-3.0]

[report-issue]: https://github.com/PrestaShop/PrestaShop/issues/new/choose
[prestashop]: https://www.prestashop.com/
[contribution-guidelines]: https://devdocs.prestashop.com/1.7/contribute/contribution-guidelines/project-modules/
[AFL-3.0]: https://opensource.org/licenses/AFL-3.0
[doc]: https://devdocs.prestashop.com/1.7/development/upgrade-module/
