# Sub-vendor directories overview

This folder contains additional `composer.json` files that updates the order of dependencies that can be loaded by Update Assistant.

They have been created to load specific versions of the same dependencies found in the main `composer.json` in order to allow the module to be compliant with a wide range of PrestaShop versions without having to maintain several versions of the same module.

This is a workaround and thus must be considered as a temporary solution. At the end, a new major version of Update Assistant will tighten the compatibility range of PrestaShop, allowing the project to go back to only one `composer.json` file.

## Prerequisites

- PHP >= 8.2
- Composer - [Download Composer](https://getcomposer.org/)

## Install project dependencies

There is nothing to do in particular here. The main `composer.json` is configured to chain the installation of the dependencies list of this folder.
