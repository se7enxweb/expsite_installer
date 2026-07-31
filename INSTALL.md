# Installing expsite_installer

## Requirements

- Exponential CMS (legacy) installation, PHP 8.1 or newer, CLI access.
- `extension/explayouts` present (provides `expLayoutsSiteInstaller`, required by the full install).
- `extension/expsite_data_media` present if you plan to run the media data-pack install.

## Steps

1. Place the extension in `extension/expsite_installer`.

2. Activate it in `settings/override/site.ini.append.php`:

   ```ini
   [ExtensionSettings]
   ActiveExtensions[]=expsite_installer
   ```

   (The installer itself will add the remaining `explayouts*` / `expsite*` extensions to this list when it runs.)

3. Regenerate the extension autoloads:

   ```bash
   php bin/php/ezpgenerateautoloads.php -e
   ```

4. Clear all caches:

   ```bash
   php bin/php/ezcache.php --clear-all --purge --allow-root-user
   ```

## Running the installer

From the installation root:

```bash
php extension/expsite_installer/bin/php/install.php
```

The script must be started from the installation root — it resolves `extension/...`, `settings/override/...` and `var/...` paths relative to the current working directory. When running as root, append `--allow-root-user`.

See `doc/USAGE.md` for what the run does and for the PHP API (`runInstall( 'media' )`, `quickSetup()`, custom paths).
