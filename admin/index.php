<?php
/**
 * This script serves as the API-handler for the TinyQueries IDE to publish queries. 
 *
 */

require_once( dirname(__FILE__) . '/../libs/TinyQueries/TinyQueries.php' );
	
$api = new TinyQueries\AdminApi( dirname(__FILE__) . '/../config/config.xml' );

$api->sendResponse();

