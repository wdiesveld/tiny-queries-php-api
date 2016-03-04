<?php
/**
 * This script serves as the API-handler. 
 *
 * You can add your own code here. The recommended way to add your own code is to 
 * extend the class TinyQueries\Api and override the method processRequest
 *
 */

require_once( dirname(__FILE__) . '/../libs/TinyQueries/TinyQueries.php' );
	
$api = new TinyQueries\Api( dirname(__FILE__) . '/../config/config.xml' );

$api->sendResponse();

