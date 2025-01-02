# Update assistant

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
* PHP >= 7.1
* Node.js >= 20 - [Download Node.js](https://nodejs.org/) (preference for LTS 20.11.0)

## Installation

All versions can be found in the [releases list](https://github.com/PrestaShop/autoupgrade/releases).

### Create a module from source code

If you download a ZIP archive that contains the source code or if you want to use the current state of the code, you need to build the module from the sources:

* Clone (`git clone https://github.com/PrestaShop/autoupgrade.git`) or [download](https://github.com/PrestaShop/autoupgrade/archive/master.zip) the source code. You can also download a release **Source code** ([ex. v4.14.2](https://github.com/PrestaShop/autoupgrade/archive/v4.14.2.zip)). If you download a source code archive, you need to extract the file and rename the extracted folder to **autoupgrade**
* Enter into folder **autoupgrade** and run the command `composer install`  ([composer](https://getcomposer.org/)).
* Enter into folder **autoupgrade/_dev** and run the commands `npm install` and `npm run build:vite` ([npm](https://docs.npmjs.com/)).
* Create a new ZIP archive from the of **autoupgrade** folder.
* Now you can install it in your shop. For example, you can upload it using the dropzone in Module Manager back office page. 

## Running an upgrade on PrestaShop

Upgrading a shop can be done using:

* the configuration page of the module (browse the back office page provided by the module)
* in command line by calling `bin/console`

### Command line parameters

This module provide a powerful command-line interface based on Symfony Console, allowing you to execute various commands
to manage your store. You can use this interface to perform updates, rollbacks, and check system requirements.

To use the Symfony Console, simply run the following command from the root directory of autoupgrade module:

```
$ php bin/console
```

The requirements can be reviewed to confirm the shop is safe to update:

```
$ php bin/console update:check <your-admin-dir>
```

A backup of the shop is created with:

```
$ php bin/console backup:create --config-file-path=[/path/to/config.json] <your-admin-dir>
```

The update process can be launched with:

```
$ php bin/console update:start --config-file-path=[/path/to/config.json] --chain <your-admin-dir>
```

You can see all available parameters and options directly from the console by using the `--help` option with any command.

For more information on using commands, please refer to the [PrestaShop developer documentation](https://devdocs.prestashop-project.org/8/basics/keeping-up-to-date/upgrade-module/upgrade-cli/)

### Configuration file

For the proper functioning of the update process via the console, it is necessary to provide a configuration file in JSON format.

Here is an example of the different fields that can be found in it:

```json
{
  "channel": "local",
  "archive_zip": "prestashop_8.0.0.zip",
  "archive_xml": "prestashop_8.0.0.xml",
  "PS_AUTOUP_CUSTOM_MOD_DESACT": 1,
  "PS_AUTOUP_CHANGE_DEFAULT_THEME": 0,
  "PS_AUTOUP_REGEN_EMAIL": 1,
  "PS_AUTOUP_KEEP_IMAGES": 1,
  "PS_DISABLE_OVERRIDES": 1
}
```

Please see the section [Configuration Parameters](#configuration-parameters) for explanations concerning the configurations

## Rollback a shop

If an error occurs during the upgrade process, the rollback will be suggested.
In case you lost the page from your backoffice, note it can be triggered via CLI.

### Command line parameters

For restore your store, you would use:

```
$ php bin/console backup:restore --backup=[backup-name]  <your-admin-dir>
```

You can see all available parameters and options directly from the console by using the `--help` option with any command.

For more information on using commands, please refer to the [PrestaShop developer documentation](https://devdocs.prestashop-project.org/8/basics/keeping-up-to-date/upgrade-module/upgrade-cli/#rollback-cli)

## Configuration Parameters

When using the command line interface (CLI), you can configure the module's behavior through a JSON file or by passing
parameters directly via CLI. Below is a detailed description of the available parameters, including their data types and
impact.

| Command                                     | Configuration file key                        | CLI option                     | Possible Values                                                                         | Description                                                                                                                                   |
|---------------------------------------------|-----------------------------------------------|--------------------------------|-----------------------------------------------------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------|
| `update:start`                              | `channel`                                     | `--channel`                    | `online` (default), `local`                                                             | Defines the update channel to use. The `local` channel requires specific files to be placed in the download folder.                           |
| `update:start`, `update:check-requirements` | `archive_zip`                                 | `--zip`                        | Valid file name                                                                         | Name of the `ZIP` file to use for an update via the archive channel. This file must be placed in `[your-admin-dir]/autoupgrade/download`.     |
| `update:start`, `update:check-requirements` | `archive_xml`                                 | `--xml`                        | Valid file name                                                                         | Name of the `XML` file corresponding to the ZIP file for the archive channel. Must also be placed in `[your-admin-dir]/autoupgrade/download`. |
| `update:start`                              | `PS_AUTOUP_CUSTOM_MOD_DESACT`                 | `--disable-non-native-modules` | `true` (default), `false`, `'true'`, `'false'`, `'1'`, `'0'`, `1`, `0`, `'on'`, `'off'` | If enabled, disables all non-native modules before the update, reducing the risk of compatibility issues.                                     |
| `update:start`                              | (DEPRECATED) `PS_AUTOUP_CHANGE_DEFAULT_THEME` | no option available            | `true`, `false` (default), `'true'`, `'false'`, `'1'`, `'0'`, `1`, `0`, `'on'`, `'off'` | If enabled, forces the use of the default PrestaShop theme after the update. If disabled, retains the current theme.                          |
| `update:start`                              | `PS_AUTOUP_REGEN_EMAIL`                       | `--regenerate-email-templates` | `true` (default), `false`, `'true'`, `'false'`, `'1'`, `'0'`, `1`, `0`, `'on'`, `'off'` | If enabled, keeps the store's customized email templates. Otherwise, the templates are replaced with the default ones.                        |
| `update:start`                              | `PS_DISABLE_OVERRIDES`                        | `--disable-all-overrides`      | `true` (default), `false`, `'true'`, `'false'`, `'1'`, `'0'`, `1`, `0`, `'on'`, `'off'` | If enabled, disables all PHP overrides in PrestaShop, ensuring better compatibility during the update process.                                |
| `backup:create`                             | `PS_AUTOUP_KEEP_IMAGES`                       | `--include-images`             | `true` (default), `false`, `'true'`, `'false'`, `'1'`, `'0'`, `1`, `0`, `'on'`, `'off'` | If enabled, retains all images in the backup. This operation can take a long time depending on the storage of your images                     |

## Documentation

* Documentation is hosted on [the Developer documentation][doc].
* Privacy documentation is hosted [on the PrestaShop Project website][prestashop-privacy].

## Use Storybook for an interface overview

The [Storybook folder](/storybook) contains a project allowing you to use Storybook to have an overview of the project interface under different versions of PrestaShop.

More information on the project [README](/storybook/README.md).

## Linting and Testing

This section outlines all the commands for code linting and testing. Before running these, ensure you've followed the project setup steps and installed all dependencies.

### Backend

All backend commands should be executed from the root directory.

- `./tests/phpstan/phpstan.sh [version]` ⮕ Runs **PHPStan**, a tool for static code analysis to identify potential errors in your PHP code (requires running a `composer install` in the `tests` folder). Available version options:
    - `1.7.2.5`
    - `1.7.3.4`
    - `1.7.4.4`
    - `1.7.5.1`
    - `1.7.6`
    - `1.7.7`
    - `1.7.8`
    - `8.0.0`
    - `latest`

- `./vendor/bin/phpunit ./tests/unit/` ⮕ Runs **PHPUnit**, a framework for running unit tests on your PHP code. You can modify the path to target specific test files.

- `./vendor/bin/php-cs-fixer` ⮕ Runs **PHP CS Fixer**, a tool that ensures your PHP code follows the correct coding standards. Add the `fix` option to automatically resolve fixable style issues.

### Frontend

All frontend commands should be executed from the `_dev` directory.

- `npm run lint` ⮕ Runs **ESLint** and **Prettier** to perform static code analysis and automatic formatting of your JavaScript code. Add `:fix` to the command to automatically fix fixable issues.

- `npm run stylelint` ⮕ Runs **Stylelint** to lint and format your SCSS files. You can append `:fix` to automatically resolve solvable formatting issues.


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
[prestashop-privacy]: https://www.prestashop-project.org/data-transparency/
[contribution-guidelines]: https://devdocs.prestashop-project.org/8/contribute/contribution-guidelines/project-modules/
[AFL-3.0]: https://opensource.org/licenses/AFL-3.0
[doc]: https://devdocs.prestashop-project.org/8/basics/keeping-up-to-date/upgrade-module/
[release-process]: https://www.prestashop-project.org/maintainers-guide/processes/release/autoupgrade/
