<?php
/**
 * This script serves as the API-handler. 
 *
 * You can add your own code here. The recommended way to add your own code is to 
 * extend the class TinyQueries\Api and override the method processRequest
 *
 */

// This will be used to catch PHP fatal errors
register_shutdown_function( '_shutdown' );

error_reporting(0);

require_once( dirname(__FILE__) . '/libs/TinyQueries/TinyQueries.php' );

$configFile = dirname(__FILE__) . '/config/config.xml';

// AdminApi is needed for publishing queries; this is only needed when the api-key is sent by the online editor
// Otherwise use the normal api class
$api = (array_key_exists('_api_key', $_REQUEST))
	? new TinyQueries\AdminApi( $configFile )
	: new TinyQueries\Api( $configFile, true );

$api->sendResponse();

/**
 * This is needed to catch fatal errors
 *
 */
function _shutdown()
{
	$error = error_get_last();

	if ($error !== NULL)
		echo json_encode( array( 'error' => 'PHP error: ' . $error['message'] . ' in ' . $error['file'] . ' line ' . $error['line'] ) );
}
 
