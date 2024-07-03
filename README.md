# 1-Click Upgrade

![PHP tests](https://github.com/PrestaShop/autoupgrade/workflows/PHP%20tests/badge.svg)
![Upgrades](https://github.com/PrestaShop/autoupgrade/workflows/Upgrades/badge.svg)
[![Latest Stable Version](https://poser.pugx.org/PrestaShop/autoupgrade/v)](//packagist.org/packages/PrestaShop/autoupgrade)
[![Total Downloads](https://poser.pugx.org/PrestaShop/autoupgrade/downloads)](//packagist.org/packages/PrestaShop/autoupgrade)
[![GitHub license](https://img.shields.io/github/license/PrestaShop/autoupgrade)](https://github.com/PrestaShop/autoupgrade/LICENSE.md)

## About

This module allows to upgrade your shop to a more recent version of PrestaShop. It can used as a CLI tool or with a web assistant.
The latest versions of the module are compatible with all PrestaShop 1.7 and higher releases.

> [!IMPORTANT]  
> This module has a specific [Release Process][release-process]. If you do release a new version, make sure to follow it.

## Branches

Branch `develop` contains code for future versions of the module, which allow upgrades from 1.7.x versions to higher.

Branch `4.14.x` contains code for `4.14.x` patch versions which allow upgrading from 1.6.x versions to 1.7.x .

If you wish to upgrade a shop powered by PrestaShop 1.6, **please use the latest 4.14.3 version** to upgrade to a 1.7 version.
Upgrades from 1.6.x to 8.x should be done in 2 steps (1.6.x to 1.7.x then 1.7.x to 8.x).

Please note PrestaShop 1.6 and older are not maintained anymore.

## Prerequisites

* PrestaShop 1.7 or 8
* PHP 7.1+

## Installation

All versions can be found in the [releases list](https://github.com/PrestaShop/autoupgrade/releases).

### Create a module from source code

If you download a ZIP archive that contains the source code or if you want to use the current state of the code, you need to build the module from the sources:

* Clone (`git clone https://github.com/PrestaShop/autoupgrade.git`) or [download](https://github.com/PrestaShop/autoupgrade/archive/master.zip) the source code. You can also download a release **Source code** ([ex. v4.14.2](https://github.com/PrestaShop/autoupgrade/archive/v4.14.2.zip)). If you download a source code archive, you need to extract the file and rename the extracted folder to **autoupgrade**
* Enter into folder **autoupgrade** and run the command `composer install`  ([composer](https://getcomposer.org/)).
* Create a new ZIP archive from the of **autoupgrade** folder.
* Now you can install it in your shop. For example, you can upload it using the dropzone in Module Manager back office page. 

## Running an upgrade on PrestaShop

Upgrading a shop can be done using:

* the configuration page of the module (browse the back office page provided by the module)
* in command line by calling the file *cli-upgrade.php*

### Command line parameters

Upgrade can be automated by calling *cli-upgrade.php*.
The following parameters are mandatory:

* **--dir**: Tells where the admin directory is.
* **--channel**: Selects what upgrade to run (minor, major etc.)
* **--action**: Advanced users only. Sets the step you want to start from (Default: `UpgradeNow`, [other values available](classes/TaskRunner/Upgrade/)).

```
$ php cli-upgrade.php --dir=admin-dev --channel=major
```

## Rollback a shop

If an error occurs during the upgrade process, the rollback will be suggested.
In case you lost the page from your backoffice, note it can be triggered via CLI.

### Command line parameters

Rollback can be automated by calling *cli-rollback.php*.
The following parameters are mandatory:

* **--dir**: Tells where the admin directory is.
* **--backup**: Select the backup to restore (this can be found in your folder `<admin>/autoupgrade/backup/`)

```
$ php cli-rollback.php  --dir=admin-dev --backup=V1.7.5.1_20190502-191341-22e883bd
```

## Documentation

Documentation is hosted on [the Developer documentation][doc].

## Use Storybook for an interface overview

The [Storybook folder](/storybook) contains a project allowing you to use Storybook to have an overview of the project interface under different versions of PrestaShop.

More information on the project [README](/storybook/README.md).

## Contributing

PrestaShop modules are open source extensions to the [PrestaShop e-commerce platform][prestashop]. Everyone is welcome and even encouraged to contribute with their own improvements!

Just make sure to follow our [contribution guidelines][contribution-guidelines].

### Reporting issues

You can report issues with this module in the main PrestaShop repository. [Click here to report an issue][report-issue].

### Translations

Wording can be translated into the [Crowdin project](https://crowdin.com/editor/prestashop-official-modules/41846).

## License

This module is released under the [Academic Free License 3.0][AFL-3.0]

[report-issue]: https://github.com/PrestaShop/PrestaShop/issues/new/choose
[prestashop]: https://www.prestashop-project.org/
[contribution-guidelines]: https://devdocs.prestashop-project.org/8/contribute/contribution-guidelines/project-modules/
[AFL-3.0]: https://opensource.org/licenses/AFL-3.0
[doc]: https://devdocs.prestashop-project.org/8/basics/keeping-up-to-date/upgrade-module/
[release-process]: https://www.prestashop-project.org/maintainers-guide/processes/release/autoupgrade/
