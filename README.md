# expsite_installer

One-command site installer for Exponential CMS (legacy). It discovers all `explayouts*` / `expsite*` extensions, activates them, runs the site data installer, regenerates autoloads and clears caches — the local equivalent of `netgen/site-installer-bundle`'s one-step site provisioning.

## Key classes

| Class | File | Purpose |
| --- | --- | --- |
| `expSiteInstaller` | `classes/expsiteinstaller.php` | Static installer facade: discovery, activation, autoloads, caches, full/media install |

CLI entry point: `bin/php/install.php` (kernel `eZScript` bootstrap, runs `runFullInstall()`).

## Methods

- `discoverExtensions()` — lists `extension/` directories starting with `explayouts` or `expsite`.
- `activateExtensions()` — merges the discovered extensions into `[ExtensionSettings]` `ActiveExtensions[]` in `settings/override/site.ini.append.php`.
- `regenerateAutoloads()` — runs `php bin/php/ezpgenerateautoloads.php -o`.
- `clearAllCaches()` — clears the global, template and content cache tags.
- `runFullInstall( $dataPath, $storagePath )` — activation + `expLayoutsSiteInstaller::install()` (schema, data, binaries) + autoloads + caches.
- `runMediaInstall( $dataPath, $storagePath )` — activation + `expSiteDataMediaInstaller::installDataPack()` (the media demo data pack) + autoloads + caches.
- `runInstall( $installType, ... )` — dispatcher: `'full'` or `'media'`.
- `quickSetup()` — activation + autoloads + caches, no data import.

## Dependencies

- `extension/explayouts` — provides the `expLayoutsSiteInstaller` base class used by the full install.
- `extension/expsite_data_media` — provides the media data pack and `expSiteDataMediaInstaller` for `runMediaInstall()`.

Package-level dependency resolution is handled by the Exponential 6 kernel package installer, which installs a package's declared dependencies before the package itself; this extension complements that by wiring up the extension activation and data-pack side.

## Provenance

Ported from `netgen/site-installer-bundle` (one-command site provisioning), reimplemented on the kernel `eZScript` / `eZINI` / `eZCache` APIs.

## Documentation

- `INSTALL.md` — activation and prerequisites
- `doc/USAGE.md` — CLI invocation and PHP API examples
- `doc/FAQ.md` — common questions
- `doc/TODO.md` — known gaps
- `doc/SUPPORT.md` — how to get help
