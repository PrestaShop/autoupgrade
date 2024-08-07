# Storybook overview

This mini-project provides an overview of the different components of the interface of the "Update assistant" module
in different versions of Prestashop.

## Prerequisites

- PHP >= 8.2
- Composer - [Download Composer](https://getcomposer.org/)
- Node.js >= 19 - [Download Node.js](https://nodejs.org/)

## Install project dependencies

**Install PHP dependencies**

To install the necessary PHP dependencies, run the following command:

```shell
# From the storybook/ folder:
$ composer install
```

**Install Node dependencies**

To install the necessary Node.js dependencies, run the following command:

```shell
$ npm install
```

## Start Local Environment

**Start Local PHP Server**

To start a local PHP server, use the following command:

```shell
$ php -S localhost:8003 -t public/
```

**Start Storybook**

To start Storybook, use the following command:

```shell
$ npm run storybook
```

Once started, you can access Storybook at: http://localhost:6006/

## Build and start environment with docker

A Compose file is provided in the `storybook/` folder. It is responsible in installing the dependencies before running PHP and Storybook.
To start Storybook via Docker, use the following command:

```shell
$ cd storybook/
$ THE_UID="$(id -u)" THE_GID="$(id -g)" docker compose up
```

Providing user and group ids allows the files created during the build to be set with the proper permissions.

Once started, you can access Storybook at: http://localhost:6006/

## Lint project files

To lint project files, use the following command:

```shell
$ npm run lint
```
