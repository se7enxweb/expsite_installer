Exponential Site Installer
===================

General description
-------------------

Exponential Site Installer (extension name: `expsite_installer`) is a
one-command site installer for Exponential CMS (eZ Publish Legacy). It is
the local equivalent of `netgen/site-installer-bundle`'s one-step site
provisioning, reimplemented on the kernel `eZScript` / `eZINI` / `eZCache`
APIs, and provides the following capabilities:

- Extension discovery - Finds all `explayouts*` / `expsite*` extensions
  present in the `extension/` directory.
- Extension activation - Merges the discovered extensions into
  `[ExtensionSettings]` `ActiveExtensions[]` in
  `settings/override/site.ini.append.php`.
- Full site install - Runs the site data installer (schema, data,
  binaries) via `expLayoutsSiteInstaller::install()`.
- Media data-pack install - Applies the `expsite_data_media` demo content
  pack on top of an existing site via
  `expSiteDataMediaInstaller::installDataPack()`.
- Autoload regeneration and cache clearing - Finishes every run with
  regenerated autoloads and cleared global, template and content caches.
- Quick setup - Activation, autoloads and caches without any data import,
  for when a new `exp*` extension has been added by hand.

Features
--------

Exponential Site Installer provides the following features in detail:

Key classes

| Class | File | Purpose |
| --- | --- | --- |
| `expSiteInstaller` | `classes/expsiteinstaller.php` | Static installer facade: discovery, activation, autoloads, caches, full/media install |

CLI entry point: `bin/php/install.php` (kernel `eZScript` bootstrap, runs
`runFullInstall()`).

Methods

- `discoverExtensions()` - lists `extension/` directories starting with
  `explayouts` or `expsite`.
- `activateExtensions()` - merges the discovered extensions into
  `[ExtensionSettings]` `ActiveExtensions[]` in
  `settings/override/site.ini.append.php`.
- `regenerateAutoloads()` - runs `php bin/php/ezpgenerateautoloads.php -o`.
- `clearAllCaches()` - clears the global, template and content cache tags.
- `runFullInstall( $dataPath, $storagePath )` - activation +
  `expLayoutsSiteInstaller::install()` (schema, data, binaries) + autoloads
  + caches.
- `runMediaInstall( $dataPath, $storagePath )` - activation +
  `expSiteDataMediaInstaller::installDataPack()` (the media demo data pack)
  + autoloads + caches.
- `runInstall( $installType, ... )` - dispatcher: `'full'` or `'media'`
  (unknown types fall through to the full install).
- `quickSetup()` - activation + autoloads + caches, no data import.

Each phase is public and can be used on its own, so custom deployment
scripts can compose the individual steps with extra phases in between.

Package dependency handling

Package-level dependency resolution is handled by the Exponential 6 kernel
package installer, which installs a package's declared dependencies before
the package itself; this extension complements that by wiring up the
extension activation and data-pack side. Use this installer for the
extension/data-pack side and the kernel package installer for `.ezpkg`
packages; the two are complementary.

Version
-------

- The current version of Exponential Site Installer is 1.0.0
- Last Major update: July 30, 2026

Copyright
---------

- Exponential Site Installer is copyright 1998 - 2026 7x
- See: [LICENSE.md](LICENSE.md) for more information on the terms of the copyright and license

License
-------

Exponential Site Installer is licensed under the GNU General Public License.

The complete license agreement is included in the LICENSE.md file.

Exponential Site Installer is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License or at your
option a later version.

Exponential Site Installer is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

The GNU GPL gives you the right to use, modify and redistribute
Exponential Site Installer under certain conditions. The GNU GPL license
is distributed with the software, see the file LICENSE.md.

It is also available at https://www.gnu.org/licenses/gpl.txt

You should have received a copy of the GNU General Public License
along with Exponential Site Installer in LICENSE.md.  If not, see https://www.gnu.org/licenses/.

Using Exponential Site Installer under the terms of the GNU GPL is free (as in freedom).

For more information or questions please contact info@se7enx.com

Requirements
------------

The following requirements exists for using Exponential Site Installer extension:

Exponential CMS / eZ Publish Legacy version
- Make sure you use Exponential 6 (eZ Publish Legacy) or higher.

PHP version
- Make sure you have PHP 8.1 or higher, with CLI access.

Sibling extensions
- `extension/explayouts` present (provides `expLayoutsSiteInstaller`,
  required by the full install).
- `extension/expsite_data_media` present if you plan to run the media
  data-pack install.

Installation
------------

Installation is the standard legacy extension procedure: place the extension
in `extension/expsite_installer`, activate it in
`settings/override/site.ini.append.php` (the installer itself will add the
remaining `explayouts*` / `expsite*` extensions to that list when it runs),
regenerate the extension autoloads and clear all caches.

See the complete step-by-step instructions in [INSTALL.md](INSTALL.md).

Usage
-----

The one-command install, from the installation root:

```bash
php extension/expsite_installer/bin/php/install.php
```

The script must be started from the installation root - it resolves
`extension/...`, `settings/override/...` and `var/...` paths relative to the
current working directory. When running as root, append
`--allow-root-user`. The script boots the kernel via `eZScript`, then calls:

```php
expSiteInstaller::runFullInstall( 'extension/expsite_data_media/data', 'var/site/storage' );
```

and prints each result section (`extensions`, `activated`,
`installer_output`, `autoload_output`, `cache_output`) to the CLI.

What a full install does, in order:

1. `discoverExtensions()` - scans `extension/` for directories starting
   with `explayouts` or `expsite`.
2. `activateExtensions()` - merges them into `[ExtensionSettings]`
   `ActiveExtensions[]` in `settings/override/site.ini.append.php`
   (rewrites the `[ExtensionSettings]` block in place).
3. `expLayoutsSiteInstaller( $dataPath, $storagePath )->install()` -
   imports schema/data SQL and copies binary storage.
4. `regenerateAutoloads()` - shells out to
   `php bin/php/ezpgenerateautoloads.php -o`.
5. `clearAllCaches()` - clears the `global`, `template` and `content`
   cache tags.

Media data-pack install (INSERT OR IGNORE delta plus storage merge, applied
on top of an existing site):

```php
$results = expSiteInstaller::runMediaInstall();
// or with explicit paths:
$results = expSiteInstaller::runMediaInstall( 'extension/expsite_data_media/data', 'var/site/storage' );
```

`runMediaInstall()` resets all INI instances and clears the INI cache before
the import so the freshly activated extensions' settings are visible, then
runs `expSiteDataMediaInstaller::installDataPack()`. The dedicated media CLI
script is `extension/expsite_data_media/bin/php/install_data.php` (see that
extension's documentation).

Dispatcher and quick setup:

```php
$results = expSiteInstaller::runInstall( 'full' );   // = runFullInstall()
$results = expSiteInstaller::runInstall( 'media' );  // = runMediaInstall()

expSiteInstaller::quickSetup();  // activation + autoloads + caches, no data import
```

Individual steps:

```php
$found  = expSiteInstaller::discoverExtensions();   // array of extension names
$result = expSiteInstaller::activateExtensions();   // array( 'message' => ..., 'extensions' => ... )
$log    = expSiteInstaller::regenerateAutoloads();  // command output as string
$msg    = expSiteInstaller::clearAllCaches();       // 'All caches cleared.'
```

See [doc/USAGE.md](doc/USAGE.md) for the full CLI and PHP API reference and
the customization guide covering all three layers: the settings layer (the
shipped `[SiteInstaller]` / `[ExtensionsToActivate]` INI keys and the
important caveat that the current code takes paths from method arguments,
not from these settings), the template layer (nothing to override; the
extension ships no templates) and the PHP layer (composing the public
static steps in your own deployment script and plugging in your own
`expLayoutsSiteInstaller` subclass).

Documentation
-------------

| Document | Description |
| --- | --- |
| [INSTALL.md](INSTALL.md) | Activation, prerequisites and how to run the installer |
| [doc/USAGE.md](doc/USAGE.md) | CLI invocation and PHP API examples plus the settings / template / PHP customization layers |
| [doc/FAQ.md](doc/FAQ.md) | Answers to the most common questions |
| [doc/TODO.md](doc/TODO.md) | Known gaps and planned improvements |
| [doc/SUPPORT.md](doc/SUPPORT.md) | How and where to get help |
| [LICENSE.md](LICENSE.md) | The complete GNU General Public License agreement |

Troubleshooting
---------------

Read the FAQ
- Some problems are more common than others. The most common ones are listed in the [doc/FAQ.md](doc/FAQ.md).

Use our support systems
- If you have any questions not handled by this document or the FAQ you can reach us through [se7enx.com](https://se7enx.com)
- If you find a bug or defect, please report it to the [Exponential Site Installer: Issue Tracker](https://github.com/se7enxweb/expsite_installer/issues)
