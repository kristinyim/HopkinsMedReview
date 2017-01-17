<?php

define( 'GD_VIP', '72.167.241.134' );
define( 'GD_RESELLER', 1 );
define( 'GD_ASAP_KEY', 'e8ec517de0bb14047298467f4fd83758' );
define( 'GD_STAGING_SITE', false );
define( 'GD_EASY_MODE', true );
define( 'GD_SITE_CREATED', 1484594522 );
define( 'REDIS_SOCKET', '/var/run/RedisProxy/mwprp.sock' );
define( 'REDIS_ACTIVE', true );

// Newrelic tracking
if ( function_exists( 'newrelic_set_appname' ) ) {
	newrelic_set_appname( '8d466cf5-d6c0-11e6-9fb6-14feb5da1938;' . ini_get( 'newrelic.appname' ) );
}

/**
 * Is this is a mobile client?  Can be used by batcache.
 * @return array
 */
function is_mobile_user_agent() {
	return array(
	       "mobile_browser"             => !in_array( $_SERVER['HTTP_X_UA_DEVICE'], array( 'bot', 'pc' ) ),
	       "mobile_browser_tablet"      => false !== strpos( $_SERVER['HTTP_X_UA_DEVICE'], 'tablet-' ),
	       "mobile_browser_smartphones" => in_array( $_SERVER['HTTP_X_UA_DEVICE'], array( 'mobile-iphone', 'mobile-smartphone', 'mobile-firefoxos', 'mobile-generic' ) ),
	       "mobile_browser_android"     => false !== strpos( $_SERVER['HTTP_X_UA_DEVICE'], 'android' )
	);
}