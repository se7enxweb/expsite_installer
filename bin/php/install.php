<?php
require_once 'autoload.php';

$script = eZScript::instance( array(
    'description' => 'Run the Exponential site installer (full install).',
    'use-session' => false,
    'use-modules' => false,
    'use-extensions' => true,
) );
$script->startup();

require_once 'extension/explayouts/classes/explayoutssiteinstaller.php';
require_once 'extension/expsite_installer/classes/expsiteinstaller.php';

$results = expSiteInstaller::runFullInstall( 'extension/expsite_data_media/data', 'var/site/storage' );

eZCLI::instance()->output( 'Exp site installer results:' );
foreach ( $results as $key => $value )
{
    if ( is_array( $value ) )
    {
        eZCLI::instance()->output( "[$key]:" );
        foreach ( $value as $line )
            eZCLI::instance()->output( '  - ' . ( is_array( $line ) ? print_r( $line, true ) : $line ) );
    }
    else
    {
        eZCLI::instance()->output( "[$key]: $value" );
    }
}

$script->shutdown();
