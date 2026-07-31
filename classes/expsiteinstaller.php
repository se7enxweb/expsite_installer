<?php

/**
 * Super installer for hourly site developers: one command to set everything up.
 */
class expSiteInstaller
{
    public static function discoverExtensions()
    {
        $extensions = array();
        $base = 'extension';
        $dir = opendir( $base );
        while ( false !== ( $entry = readdir( $dir ) ) )
        {
            if ( $entry === '.' || $entry === '..' || !is_dir( $base . '/' . $entry ) )
                continue;
            if ( strpos( $entry, 'explayouts' ) === 0 || strpos( $entry, 'expsite' ) === 0 )
                $extensions[] = $entry;
        }
        closedir( $dir );
        sort( $extensions );
        return $extensions;
    }

    public static function activateExtensions()
    {
        $extensions = self::discoverExtensions();
        $siteIniPath = 'settings/override/site.ini.append.php';
        $siteIni = eZINI::instance( 'site.ini' );
        $active = $siteIni->variable( 'ExtensionSettings', 'ActiveExtensions' );
        if ( !is_array( $active ) )
            $active = array();

        $changed = false;
        foreach ( $extensions as $ext )
        {
            if ( !in_array( $ext, $active, true ) )
            {
                $active[] = $ext;
                $changed = true;
            }
        }

        if ( !$changed )
            return array( 'message' => 'All extensions already active.', 'extensions' => $active );

        $content = file_exists( $siteIniPath ) ? file_get_contents( $siteIniPath ) : "<?php /* #?ini charset=\"utf-8\"?\n";
        $block = "[ExtensionSettings]\nActiveExtensions[]=" . implode( "\nActiveExtensions[]=", $active ) . "\n";
        if ( strpos( $content, '[ExtensionSettings]' ) !== false )
            $content = preg_replace( '/\[ExtensionSettings\][^\[]*/', $block, $content );
        else
            $content .= "\n" . $block;
        file_put_contents( $siteIniPath, $content );
        return array( 'message' => 'Activated extensions.', 'extensions' => $active );
    }

    public static function regenerateAutoloads()
    {
        $output = array();
        exec( 'cd ' . escapeshellarg( getcwd() ) . ' && php bin/php/ezpgenerateautoloads.php -o 2>&1', $output );
        return implode( "\n", $output );
    }

    public static function clearAllCaches()
    {
        eZCache::clearByTag( 'global' );
        eZCache::clearByTag( 'template' );
        eZCache::clearByTag( 'content' );
        return 'All caches cleared.';
    }

    public static function runFullInstall( $dataPath = 'extension/expsite_data_media/data/netgen-media', $storagePath = 'var/ezwebin_site/storage' )
    {
        $results = array();
        $results['extensions'] = self::discoverExtensions();
        $results['activated'] = self::activateExtensions();
        $installer = new expLayoutsSiteInstaller( $dataPath, $storagePath );
        $results['installer_output'] = $installer->install();
        $results['autoload_output'] = self::regenerateAutoloads();
        $results['cache_output'] = self::clearAllCaches();
        return $results;
    }

    public static function quickSetup()
    {
        self::activateExtensions();
        self::regenerateAutoloads();
        return self::clearAllCaches();
    }

    /**
     * Installs the expsite_data_media data pack on top of an existing site.
     * This imports the media SQL data (eztags) and merges the extra binaries
     * into the live eZ4 storage directory.
     */
    public static function runMediaInstall( $dataPath = null, $storagePath = null )
    {
        require_once 'extension/expsite_data_media/classes/expsitedatamediainstaller.php';

        $results = array();
        $results['extensions'] = self::discoverExtensions();
        $results['activated'] = self::activateExtensions();

        // Make sure newly-activated extension INIs are visible before the
        // media installer reads its configuration.
        eZINI::resetAllInstances();
        eZCache::clearByTag( 'ini' );

        $dataPath = $dataPath ?: null;
        $storagePath = $storagePath ?: null;
        $installer = new expSiteDataMediaInstaller( $dataPath, $storagePath );
        $results['installer_output'] = $installer->installDataPack();

        $results['autoload_output'] = self::regenerateAutoloads();
        $results['cache_output'] = self::clearAllCaches();
        return $results;
    }

    /**
     * Dispatch helper for the main site installer types.
     * Supported $installType values: 'full', 'media'.
     */
    public static function runInstall( $installType, $dataPath = null, $storagePath = null )
    {
        $installType = strtolower( $installType );
        if ( $installType === 'media' )
            return self::runMediaInstall( $dataPath, $storagePath );
        return self::runFullInstall( $dataPath, $storagePath );
    }
}
