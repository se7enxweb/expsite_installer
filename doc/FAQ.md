# expsite_installer FAQ

## Which extensions does it activate?

Every directory under `extension/` whose name starts with `explayouts` or `expsite`, sorted alphabetically. The list is merged (no duplicates) into `[ExtensionSettings]` `ActiveExtensions[]` in `settings/override/site.ini.append.php`.

## Where does it write configuration?

Only `settings/override/site.ini.append.php`. If the file has an `[ExtensionSettings]` block it is replaced in place; otherwise the block is appended. Existing comments inside that block are not preserved — keep a backup if you maintain hand-written notes there.

## What is the difference between the full install and the media install?

`runFullInstall()` uses `expLayoutsSiteInstaller::install()` — schema import, data import and binary copy for a fresh site. `runMediaInstall()` uses `expSiteDataMediaInstaller::installDataPack()` — an INSERT OR IGNORE delta applied on top of an existing site plus a storage merge (it will not overwrite existing rows such as the Home node).

## Why must the script run from the installation root?

`expSiteInstaller` and `bin/php/install.php` use relative paths (`extension`, `settings/override/site.ini.append.php`, `var/...`, `bin/php/ezpgenerateautoloads.php`). Started from anywhere else, discovery and activation fail or write to the wrong tree.

## Are its INI settings honoured?

Not yet. `[SiteInstaller]` `DataPath` / `StoragePath` and `[ExtensionsToActivate]` are declared in `settings/expsite_installer.ini.append.php` but the class reads none of them; paths come from method arguments. See `TODO.md`.

## Is clearAllCaches() the same as ezcache.php --clear-all?

No. It clears the `global`, `template` and `content` cache tags via `eZCache::clearByTag()`. For a guaranteed clean slate run `php bin/php/ezcache.php --clear-all --purge --allow-root-user` afterwards.
