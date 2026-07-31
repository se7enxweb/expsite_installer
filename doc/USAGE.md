# Using expsite_installer

All examples use real class and method names from `classes/expsiteinstaller.php` and `bin/php/install.php`.

## The one-command install

From the installation root:

```bash
php extension/expsite_installer/bin/php/install.php
```

The script boots the kernel via `eZScript`, then calls:

```php
expSiteInstaller::runFullInstall( 'extension/expsite_data_media/data', 'var/site/storage' );
```

and prints each result section (`extensions`, `activated`, `installer_output`, `autoload_output`, `cache_output`) to the CLI. Append `--allow-root-user` when running as root.

What a full install does, in order:

1. `discoverExtensions()` — scans `extension/` for directories starting with `explayouts` or `expsite`.
2. `activateExtensions()` — merges them into `[ExtensionSettings]` `ActiveExtensions[]` in `settings/override/site.ini.append.php` (rewrites the `[ExtensionSettings]` block in place).
3. `expLayoutsSiteInstaller( $dataPath, $storagePath )->install()` — imports schema/data SQL and copies binary storage.
4. `regenerateAutoloads()` — shells out to `php bin/php/ezpgenerateautoloads.php -o`.
5. `clearAllCaches()` — clears the `global`, `template` and `content` cache tags.

## Media data-pack install

Installs the `expsite_data_media` demo content on top of an existing site (INSERT OR IGNORE delta plus storage merge):

```php
$results = expSiteInstaller::runMediaInstall();
// or with explicit paths:
$results = expSiteInstaller::runMediaInstall( 'extension/expsite_data_media/data', 'var/site/storage' );
```

`runMediaInstall()` resets all INI instances and clears the INI cache before the import so the freshly activated extensions' settings are visible, then runs `expSiteDataMediaInstaller::installDataPack()`. The dedicated media CLI script is `extension/expsite_data_media/bin/php/install_data.php` (see that extension's `doc/USAGE.md`).

## Dispatcher

```php
$results = expSiteInstaller::runInstall( 'full' );   // = runFullInstall()
$results = expSiteInstaller::runInstall( 'media' );  // = runMediaInstall()
```

Unknown types fall through to the full install.

## Quick setup without data import

Activates extensions, regenerates autoloads and clears caches — useful after adding a new `exp*` extension by hand:

```php
expSiteInstaller::quickSetup();
```

## Individual steps

Each phase is public and can be used on its own:

```php
$found  = expSiteInstaller::discoverExtensions();   // array of extension names
$result = expSiteInstaller::activateExtensions();   // array( 'message' => ..., 'extensions' => ... )
$log    = expSiteInstaller::regenerateAutoloads();  // command output as string
$msg    = expSiteInstaller::clearAllCaches();       // 'All caches cleared.'
```

## Package dependencies

When installing packages through the Exponential 6 kernel package installer, a package's declared dependencies (its required packages) are installed before the package itself. Use this installer for the extension/data-pack side and the kernel package installer for `.ezpkg` packages; the two are complementary.

## Customization

### Settings layer

`settings/expsite_installer.ini.append.php` declares:

```ini
[SiteInstaller]
DataPath=extension/expsite_data_media/data/netgen-media
StoragePath=var/ezwebin_site/storage

[ExtensionsToActivate]
ActivateAllExpExtensions=enabled
```

Note: the current `expSiteInstaller` code does not read these settings — paths come from method arguments (and `bin/php/install.php` passes `extension/expsite_data_media/data` and `var/site/storage`), and the activation set is hardcoded to the `explayouts*` / `expsite*` prefixes (see `TODO.md`). If you need different paths, pass them explicitly:

```php
expSiteInstaller::runFullInstall( 'extension/my_data_pack/data', 'var/mysite/storage' );
```

The INI cascade (extension defaults → `settings/siteaccess/<sa>/` → extension siteaccess → `settings/override/`) applies to these keys once code starts consuming them.

### Template layer

The extension ships no templates or design directory; there is nothing to override in the design cascade.

### PHP layer

All methods are `public static`, so composition is the extension point:

- Call the individual steps from your own deployment script instead of `runFullInstall()` when you need extra phases in between.
- For a different data installer, instantiate your own `expLayoutsSiteInstaller` subclass and call the steps around it — `expSiteDataMediaInstaller` in `expsite_data_media` is the reference example of such a subclass.
- `discoverExtensions()`/`activateExtensions()` are static and not overridable by subclass polymorphism at the call sites inside `runFullInstall()`; wrap them rather than subclassing.
