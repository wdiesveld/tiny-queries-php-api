<?php
/**
 * This script serves as the API-handler. 
 *
 * You can add your own code here. The recommended way to add your own code is to 
 * extend the class TinyQueries\Api and override the method processRequest
 *
 */

require_once( dirname(__FILE__) . '/libs/TinyQueries/TinyQueries.php' );

$configFile = dirname(__FILE__) . '/config/config.xml';

// AdminApi is needed for publishing queries; this is only needed when the api-key is sent by the online editor
// Otherwise use the normal api class
$api = (array_key_exists('_api_key', $_REQUEST))
	? new TinyQueries\AdminApi( $configFile )
	: new TinyQueries\Api( $configFile, true );

$api->sendResponse();
