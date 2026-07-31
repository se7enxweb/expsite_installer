# expsite_installer TODO

Code-observed gaps; no promises attached.

- `settings/expsite_installer.ini.append.php` declares `[SiteInstaller]` `DataPath` / `StoragePath` and `[ExtensionsToActivate]` `ActivateAllExpExtensions`, but `expSiteInstaller` never reads them; the defaults in code and the paths passed by `bin/php/install.php` are the only sources.
- The `runFullInstall()` default arguments (`extension/expsite_data_media/data/netgen-media`, `var/ezwebin_site/storage`) do not match the shipped data pack layout (`extension/expsite_data_media/data`, `var/site/storage`); `bin/php/install.php` passes the correct paths, but calling `runFullInstall()` without arguments uses the stale defaults.
- `activateExtensions()` rewrites the whole `[ExtensionSettings]` block in `settings/override/site.ini.append.php` with a regex, dropping any comments or non-`ActiveExtensions` keys that were inside that block.
- `clearAllCaches()` only clears the `global`, `template` and `content` tags — narrower than `ezcache.php --clear-all --purge`.
- `regenerateAutoloads()` shells out with `exec()` and returns raw output; failures are not detected via exit code.
- All paths are relative to the current working directory; there is no root-path detection or guard when started elsewhere.
