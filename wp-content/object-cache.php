<?php

/*
 * Plugin Name: APC/APCu/Redis Object Cache - transitional
 * Description: APC/APCu/Redis backend for the WP Object Cache.
 * Version 1.0
 *
 * @Author James Dugger
 * @copyright 2017 GoDaddy Inc. 14455 N. Hayden Road Scottsdale, Arizona
 */

$oc_logged_in = false;

foreach ( $_COOKIE as $k => $v ) {

	if ( preg_match( '/^comment_author|wordpress_logged_in_[a-f0-9]+|woocommerce_items_in_cart|PHPSESSID_|edd_wp_session|edd_items_in_cartcc_cart_key|ccm_token/', $k ) ) {

		$oc_logged_in = true;

	}

}

$oc_blocked_page = ( defined( 'WP_ADMIN' ) || defined( 'DOING_AJAX' ) || defined( 'XMLRPC_REQUEST' ) || 'wp-login.php' === basename( $_SERVER['SCRIPT_FILENAME'] ) );

function wpaas_is_using_redis() {

	return version_compare( PHP_VERSION, '5.6.0', '>=' ) && defined( 'REDIS_ACTIVE' ) && true === REDIS_ACTIVE && defined( 'REDIS_SOCKET' );

}

function wpaas_is_using_apcu() {

	return version_compare( PHP_VERSION, '5.6.0', '>=' ) && function_exists( 'apcu_fetch' ) && ( ! defined( 'REDIS_ACTIVE' ) || false === REDIS_ACTIVE );

}


if ( 'cli' !== php_sapi_name() && ! $oc_logged_in && ! $oc_blocked_page
	&& ( wpaas_is_using_redis() || wpaas_is_using_apcu() ) ) :

	/**
	 * Save the transients to the DB.  The explanation is a bit too long
	 * for code.  The tl;dr of it is that we don't have a single 'fast cache'
	 * source yet (like memcached) and so some long lived items like transients
	 * are still best cached in the db and then brought back into APC
	 *
	 * @param string  $transient
	 * @param mixed   $value
	 * @param int     $expire
	 * @param boolean $site = false
	 *
	 * @return bool
	 */
	function wpaas_save_transient( $transient, $value, $expire, $site = false ) {
		global $wp_object_cache, $wpdb;

		// The 'special' transient option names
		$transient_timeout = ( $site ? '_site' : '' ) . '_transient_timeout_' . $transient;
		$transient         = ( $site ? '_site' : '' ) . '_transient_' . $transient;

		// Cap expiration at 24 hours to avoid littering the DB
		if ( $expire == 0 ) {
			$expire = 24 * 60 * 60;
		}

		// Save to object cache
		$wp_object_cache->set( $transient, $value, 'options', $expire );
		$wp_object_cache->set( $transient_timeout, time() + $expire, 'options', $expire );

		// Update alloptions
		$alloptions                       = $wp_object_cache->get( 'alloptions', 'options' );
		$alloptions[ $transient ]         = $value;
		$alloptions[ $transient_timeout ] = time() + $expire;
		$wp_object_cache->set( 'alloptions', $alloptions, 'options' );

		// Use the normal update option logic
		if ( ! empty( $wpdb ) && $wpdb instanceof wpdb ) {
			$flag = $wpdb->suppress_errors;
			$wpdb->suppress_errors( true );
			if ( $site && is_multisite() ) {
				$wpdb->query( $wpdb->prepare( "INSERT INTO `{$wpdb->sitemeta}` (`option_name`, `option_value`, `autoload`) VALUES (%s, UNIX_TIMESTAMP( NOW() ) + %d, %s) ON DUPLICATE KEY UPDATE `option_name` = VALUES(`option_name`), `option_value` = VALUES(`option_value`), `autoload` = VALUES(`autoload`)",
					$transient_timeout, $expire, 'yes' ) );
				$wpdb->query( $wpdb->prepare( "INSERT INTO `{$wpdb->sitemeta}` (`option_name`, `option_value`, `autoload`) VALUES (%s, %s, %s) ON DUPLICATE KEY UPDATE `option_name` = VALUES(`option_name`), `option_value` = VALUES(`option_value`), `autoload` = VALUES(`autoload`)",
					$transient, maybe_serialize( $value ), 'no' ) );
			} else {
				$wpdb->query( $wpdb->prepare( "INSERT INTO `{$wpdb->options}` (`option_name`, `option_value`, `autoload`) VALUES (%s, UNIX_TIMESTAMP( NOW() ) + %d, %s) ON DUPLICATE KEY UPDATE `option_name` = VALUES(`option_name`), `option_value` = VALUES(`option_value`), `autoload` = VALUES(`autoload`)",
					$transient_timeout, $expire, 'yes' ) );
				$wpdb->query( $wpdb->prepare( "INSERT INTO `{$wpdb->options}` (`option_name`, `option_value`, `autoload`) VALUES (%s, %s, %s) ON DUPLICATE KEY UPDATE `option_name` = VALUES(`option_name`), `option_value` = VALUES(`option_value`), `autoload` = VALUES(`autoload`)",
					$transient, maybe_serialize( $value ), 'no' ) );
			}
			$wpdb->suppress_errors( $flag );
		}

		return true;
	}

	function wpaas_prune_transients() {
		global $wpdb;

		if ( ! empty( $wpdb ) && $wpdb instanceof wpdb && function_exists( 'is_main_site' ) && function_exists( 'is_main_network' ) ) {

			$flag = $wpdb->suppress_errors;
			$wpdb->suppress_errors( true );

			// Lifted straight from schema.php

			// Deletes all expired transients.
			// The multi-table delete syntax is used to delete the transient record from table a,
			// and the corresponding transient_timeout record from table b.
			$time = time();
			$wpdb->query( "DELETE a, b FROM $wpdb->options a, $wpdb->options b WHERE
		a.option_name LIKE '\_transient\_%' AND
		a.option_name NOT LIKE '\_transient\_timeout\_%' AND
		b.option_name = CONCAT( '_transient_timeout_', SUBSTRING( a.option_name, 12 ) )
		AND b.option_value < $time" );

			if ( is_main_site() && is_main_network() ) {
				$wpdb->query( "DELETE a, b FROM $wpdb->options a, $wpdb->options b WHERE
		a.option_name LIKE '\_site\_transient\_%' AND
		a.option_name NOT LIKE '\_site\_transient\_timeout\_%' AND
		b.option_name = CONCAT( '_site_transient_timeout_', SUBSTRING( a.option_name, 17 ) )
		AND b.option_value < $time" );
			}


			$wpdb->suppress_errors( $flag );
		}
	}

	/**
	 * If another cache was flushed or updated, sync across all servers / processes using
	 * the database as the authority.  This uses the database as the authority for timestamps
	 * as well to avoid drift between servers.
	 * @return void
	 */
	function wpaas_init_sync_cache() {

		global $wpdb;

		if ( empty( $wpdb ) || ! ( $wpdb instanceof wpdb ) ) {

			return;

		}

		$flag = $wpdb->suppress_errors;
		$wpdb->suppress_errors( true );
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT option_name, option_value FROM `{$wpdb->options}` WHERE option_name = '%s' UNION SELECT 'current_time', UNIX_TIMESTAMP(NOW()) AS option_value", 'gd_system_last_cache_flush' ), ARRAY_A );
		$wpdb->suppress_errors( $flag );

		if ( empty( $result ) ) {

			return;

		}

		$master_flush = false;

		foreach ( $result as $row ) {

			switch ( $row['option_name'] ) {

				case 'current_time' :
					$current_time = $row['option_value'];
					break;

				case 'gd_system_last_cache_flush' :
					$master_flush = $row['option_value'];
					break;

			}

		}

		$local_flush = wp_cache_get( 'gd_system_last_cache_flush' );

		if ( false === $local_flush || $local_flush < $master_flush ) {

			wp_cache_flush( true );

			wp_cache_set( 'gd_system_last_cache_flush', $current_time );

		}

	}

	/**
	 * Start default implementation of object cache
	 */

	if ( ! defined( 'WP_APC_KEY_SALT' ) ) {

		define( 'WP_APC_KEY_SALT', '' );

	}

	if ( ! defined( 'WP_CACHE_KEY_SALT' ) ) {

		define( 'WP_CACHE_KEY_SALT', '' );

	}


	function wp_cache_add( $key, $data, $group = '', $expire = 0 ) {
		global $wp_object_cache;

		if ( 'transient' == $group ) {
			wpaas_save_transient( $key, $data, $expire );

			return $wp_object_cache->add( "_transient_$key", $data, 'options', $expire );
		} elseif ( 'site-transient' == $group ) {
			wpaas_save_transient( $key, $data, $expire, true );

			return $wp_object_cache->add( "_site_transient_$key", $data, 'site-options', $expire );
		} else {
			return $wp_object_cache->add( $key, $data, $group, $expire );
		}
	}

	function wp_cache_incr( $key, $n = 1, $group = '' ) {
		global $wp_object_cache;

		return $wp_object_cache->incr2( $key, $n, $group );
	}

	function wp_cache_decr( $key, $n = 1, $group = '' ) {
		global $wp_object_cache;

		return $wp_object_cache->decr( $key, $n, $group );
	}

	function wp_cache_close() {
		return true;
	}

	function wp_cache_delete( $key, $group = '' ) {
		global $wp_object_cache, $wpdb;

		if ( 'transient' == $group ) {
			if ( ! empty( $wpdb ) && $wpdb instanceof wpdb ) {
				$flag = $wpdb->suppress_errors;
				$wpdb->suppress_errors( true );
				$wpdb->query( $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}options` WHERE option_name IN ( '%s', '%s' )",
					"_transient_$key", "_transient_timeout_$key" ) );
				$wpdb->suppress_errors( $flag );
			}

			$wp_object_cache->delete( "_transient_timeout_$key", 'options' );

			// Update alloptions
			$alloptions = $wp_object_cache->get( 'alloptions', 'options' );
			unset( $alloptions["_transient_$key"] );
			unset( $alloptions["_transient_timeout_$key"] );
			$wp_object_cache->set( 'alloptions', $alloptions, 'options' );

			return $wp_object_cache->delete( "_transient_$key", 'options' );
		} elseif ( 'site-transient' == $group ) {
			if ( ! empty( $wpdb ) && $wpdb instanceof wpdb ) {
				$table = $wpdb->options;
				if ( is_multisite() ) {
					$table = $wpdb->sitemeta;
				}
				$flag = $wpdb->suppress_errors;
				$wpdb->suppress_errors( true );
				$wpdb->query( $wpdb->prepare( "DELETE FROM `{$wpdb->sitemeta}` WHERE option_name IN ( '%s', '%s' )",
					"_transient_$key", "_transient_timeout_$key" ) );
				$wpdb->suppress_errors( $flag );
			}
			$wp_object_cache->delete( "_transient_timeout_$key", 'site-options' );

			// Update alloptions
			$alloptions = $wp_object_cache->get( 'alloptions', 'options' );
			unset( $alloptions["_site_transient_$key"] );
			unset( $alloptions["_site_transient_timeout_$key"] );
			$wp_object_cache->set( 'alloptions', $alloptions, 'options' );

			return $wp_object_cache->delete( "_site_transient_$key", 'site-options' );
		}

		return $wp_object_cache->delete( $key, $group );
	}

	function wp_cache_flush( $local_flush = false ) {
		global $wp_object_cache, $wpdb;

		if ( ! $local_flush ) {
			if ( ! empty( $wpdb ) && $wpdb instanceof wpdb ) {
				$flag = $wpdb->suppress_errors;
				$wpdb->suppress_errors( true );
				$wpdb->query( $wpdb->prepare( "INSERT INTO `{$wpdb->options}` (`option_name`, `option_value`, `autoload`) VALUES (%s, UNIX_TIMESTAMP(NOW()), '%s') ON DUPLICATE KEY UPDATE `option_name` = VALUES(`option_name`), `option_value` = VALUES(`option_value`), `autoload` = VALUES(`autoload`)",
					'gd_system_last_cache_flush', 'no' ) );
				$wpdb->suppress_errors( $flag );
			}
		}

		return $wp_object_cache->flush();
	}

	function wp_cache_get( $key, $group = '', $force = false ) {
		global $wp_object_cache, $wpdb;

		if ( 'transient' == $group ) {
			$alloptions = $wp_object_cache->get( 'alloptions', 'options' );
			if ( isset( $alloptions["_transient_$key"] ) && isset( $alloptions["_transient_timeout_$key"] ) && $alloptions["_transient_timeout_$key"] > time() ) {
				return maybe_unserialize( $alloptions["_transient_$key"] );
			}
			$transient = $wp_object_cache->get( "_transient_$key", 'options', $force );
			$timeout   = $wp_object_cache->get( "_transient_timeout_$key", 'options', $force );
			if ( false !== $transient && ! empty( $timeout ) && $timeout > time() ) {
				return maybe_unserialize( $transient );
			}
			if ( ! empty( $wpdb ) && $wpdb instanceof wpdb ) {
				$flag = $wpdb->suppress_errors;
				$wpdb->suppress_errors( true );
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT option_name, option_value FROM `{$wpdb->options}` WHERE option_name IN( '%s', '%s' ) UNION SELECT 'current_time', UNIX_TIMESTAMP(NOW()) AS option_value",
					"_transient_$key", "_transient_timeout_$key" ), ARRAY_A );
				$wpdb->suppress_errors( $flag );
				if ( ! empty( $result ) ) {
					$transient    = false;
					$timeout      = false;
					$current_time = time();
					foreach ( $result as $row ) {
						switch ( $row['option_name'] ) {
							case "_transient_$key" :
								$transient = $row['option_value'];
								break;
							case "_transient_timeout_$key" :
								$timeoout = $row['option_value'];
								break;
							case '_current_time' :
								$current_time = $row['option_value'];
								break;
						}
					}
					if ( false !== $transient && ! empty( $timeout ) && $timeout > $current_time ) {
						return maybe_unserialize( $transient );
					}
				}
			}

			return false;
		} elseif ( 'site-transient' == $group ) {
			$transient = $wp_object_cache->get( "_site_transient_$key", 'options', $force );
			$timeout   = $wp_object_cache->get( "_site_transient_timeout_$key", 'options', $force );
			if ( false !== $transient && ! empty( $timeout ) && $timeout > time() ) {
				return maybe_unserialize( $transient );
			}
			if ( ! empty( $wpdb ) && $wpdb instanceof wpdb ) {
				$table = $wpdb->options;
				if ( is_multisite() ) {
					$table = $wpdb->sitemeta;
				}
				$flag = $wpdb->suppress_errors;
				$wpdb->suppress_errors( true );
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT option_name, option_value FROM `{$table}` WHERE option_name IN( '%s', '%s' ) UNION SELECT 'current_time', UNIX_TIMESTAMP(NOW()) AS option_value",
					"_site_transient_$key", "_site_transient_timeout_$key" ), ARRAY_A );
				$wpdb->suppress_errors( $flag );
				if ( ! empty( $result ) ) {
					$transient    = false;
					$timeout      = false;
					$current_time = time();
					foreach ( $result as $row ) {
						switch ( $row['option_name'] ) {
							case "_site_transient_$key" :
								$transient = $row['option_value'];
								break;
							case "_site_transient_timeout_$key" :
								$timeoout = $row['option_value'];
								break;
							case '_current_time' :
								$current_time = $row['option_value'];
								break;
						}
					}
					if ( false !== $transient && ! empty( $timeout ) && $timeout > $current_time ) {
						return maybe_unserialize( $transient );
					}
				}
			}

			return false;
		} else {
			return $wp_object_cache->get( $key, $group, $force );
		}
	}

	function wp_cache_init() {
		global $wp_object_cache;

		if ( mt_rand( 1, 100 ) == 42 ) {

			wpaas_prune_transients();

		}

		add_action( 'muplugins_loaded', 'wpaas_init_sync_cache' );

		if ( wpaas_is_using_redis() ) {

			$wp_object_cache = new Redis_Object_Cache();

			return;

		}

		$wp_object_cache = new APCu_Object_Cache();

	}

	function wp_cache_replace( $key, $data, $group = '', $expire = 0 ) {
		global $wp_object_cache;

		return $wp_object_cache->replace( $key, $data, $group, $expire );
	}

	function wp_cache_set( $key, $data, $group = '', $expire = 0 ) {
		global $wp_object_cache;

		if ( defined( 'WP_INSTALLING' ) == false ) {
			if ( 'transient' == $group ) {
				return wpaas_save_transient( $key, $data, $expire );
			} elseif ( 'site-transient' == $group ) {
				return wpaas_save_transient( $key, $data, $expire, true );
			} else {
				return $wp_object_cache->set( $key, $data, $group, $expire );
			}
		} else {
			return $wp_object_cache->delete( $key, $group );
		}
	}

	function wp_cache_switch_to_blog( $blog_id ) {
		global $wp_object_cache;

		return $wp_object_cache->switch_to_blog( $blog_id );
	}

	function wp_cache_add_global_groups( $groups ) {
		global $wp_object_cache;

		$wp_object_cache->add_global_groups( $groups );
	}

	function wp_cache_add_non_persistent_groups( $groups ) {
		global $wp_object_cache;

		$wp_object_cache->add_non_persistent_groups( $groups );
	}

	class Redis_Object_Cache {

		/**
		 *
		 * Holds the cached objects
		 *
		 * @var array $cache
		 * @access public
		 */
		public $cache = [];

		/**
		 * The amount of times the cache data was already stored in the cache.
		 *
		 *
		 * @var int $cache_hits
		 * @access public
		 */
		public $cache_hits = 0;

		/**
		 * Amount of times the cache did not have the request in cache
		 *
		 * @var int $cache_misses
		 * @access public
		 */
		public $cache_misses = 0;

		/**
		 * The amount of times a request was made to Redis
		 *
		 * @access public
		 * @var int $redis_calls
		 */
		public $redis_calls = [];

		/**
		 * List of global groups
		 *
		 * @var array $global_groups
		 * @access public
		 */
		public $global_groups = [];

		/**
		 * List of non-persistent groups
		 *
		 * @var array $non_persistent_groups
		 * @access public
		 */
		public $non_persistent_groups = [];

		/**
		 * The blog prefix to prepend to keys in non-global groups.
		 *
		 * @var int $blog_prefix
		 * @access public
		 */
		public $blog_prefix;

		/**
		 * Whether or not Redis is connected
		 *
		 * @var bool $is_connected
		 * @access public
		 */
		public $is_connected = false;

		/**
		 * The last triggered error
		 *
		 * @var string $last_triggered_error
		 * @access public
		 */
		public $last_triggered_error = '';

		/**
		 * Max TTL for a cache item, in seconds
		 * @var int $max_cache_ttl
		 * @access public
		 */
		public $max_cache_ttl = 604800;

		/**
		 * Minimum TTL for a cache item, in seconds
		 * @var int $default_expire
		 * @access public
		 */
		public $default_expire = 86400;

		/**
		 * Minimum response message size
		 * @var int $min_read_size
		 * @access public
		 */
		public $min_read_size = 9;

		/**
		 * Max time to wait to get a response from redis
		 * @var int $max_read_time
		 * @access public
		 */
		public $max_read_time = 5;


		/**
		 * Adds data to the cache if it doesn't already exist.
		 *
		 * @uses WP_Object_Cache::_exists Checks to see if the cache already has data.
		 * @uses WP_Object_Cache::set Sets the data after the checking the cache
		 *      contents existence.
		 *
		 * @param int|string $key What to call the contents in the cache
		 * @param mixed $data The contents to store in the cache
		 * @param string $group Where to group the cache contents
		 * @param int $expire When to expire the cache contents
		 *
		 * @return bool False if cache key and group already exist, true on success
		 */
		public function add( $key, $data, $group = 'default', $expire = 0 ) {

			if ( empty( $group ) ) {

				$group = 'default';

			}

			if ( function_exists( 'wp_suspend_cache_addition' ) && wp_suspend_cache_addition() ) {

				return false;

			}

			if ( $this->_exists( $key, $group ) ) {

				return false;

			}

			return $this->set( $key, $data, $group, (int) $expire );

		}

		/**
		 * Sets the list of global groups.
		 *
		 * @param array $groups List of groups that are global.
		 */
		public function add_global_groups( $groups ) {

			$groups = (array) $groups;

			$groups = array_fill_keys( $groups, true );

			$this->global_groups = array_merge( $this->global_groups, $groups );

		}

		/**
		 * Sets the list of non-persistent groups.
		 *
		 * @param array $groups List of groups that are non-persistent.
		 */
		public function add_non_persistent_groups( $groups ) {

			$groups = (array) $groups;

			$groups = array_fill_keys( $groups, true );

			$this->non_persistent_groups = array_merge( $this->non_persistent_groups, $groups );

		}

		/**
		 * Decrement numeric cache item's value
		 *
		 * @param int|string $key The cache key to increment
		 * @param int $offset The amount by which to decrement the item's value. Default is 1.
		 * @param string $group The group the key is in.
		 *
		 * @return false|int False on failure, the item's new value on success.
		 */
		public function decr( $key, $offset = 1, $group = 'default' ) {

			if ( empty( $group ) ) {

				$group = 'default';

			}

			// The key needs to exist in order to be decremented
			if ( ! $this->_exists( $key, $group ) ) {

				return false;

			}

			$offset = (int) $offset;

			# If this isn't a persistant group, we have to sort this out ourselves, grumble grumble
			if ( ! $this->_should_persist( $group ) ) {

				$existing = $this->_get_internal( $key, $group );

				if ( empty( $existing ) || ! is_numeric( $existing ) ) {

					$existing = 0;

				} else {

					$existing -= $offset;

				}

				if ( $existing < 0 ) {

					$existing = 0;

				}

				$this->_set_internal( $key, $group, $existing );

				return $existing;
			}

			$id = $this->_key( $key, $group );

			$result = $this->_call_redis( $this->_create_container( 'incrby', $id, - $offset ) );

			if ( $result < 0 ) {

				$result = 0;

				$this->_call_redis( $this->_create_container( 'set', $id, $result ) );

			}

			if ( is_int( $result ) ) {

				$this->_set_internal( $key, $group, $result );

			}

			return $result;
		}

		/**
		 * Remove the contents of the cache key in the group
		 *
		 * If the cache key does not exist in the group and $force parameter is set
		 * to false, then nothing will happen. The $force parameter is set to false
		 * by default.
		 *
		 * @param int|string $key What the contents in the cache are called
		 * @param string $group Where the cache contents are grouped
		 * @param bool $force Optional. Whether to force the unsetting of the cache
		 *      key in the group
		 *
		 * @return bool False if the contents weren't deleted and true on success
		 */
		public function delete( $key, $group = 'default', $force = false ) {

			if ( empty( $group ) ) {

				$group = 'default';

			}

			if ( ! $force && ! $this->_exists( $key, $group ) ) {

				return false;

			}

			if ( $this->_should_persist( $group ) ) {

				$id = $this->_key( $key, $group );

				$this->_call_redis( $this->_create_container( 'del', $id ) );

			}

			$this->_unset_internal( $key, $group );

			return true;

		}

		/**
		 * Remove the contents of all cache keys in the group.
		 *
		 * We don't really support this currently, but may in the future.
		 * Keeping the method here for future enhancement opportunity, and
		 * to properly support the WordPress cache API
		 *
		 * @return boolean True on success, false on failure.
		 */
		public function delete_group() {

			return false;

		}

		/**
		 * Clears the object cache of all data.
		 *
		 * By default, this will flush the session cache as well as Redis, but we
		 * can leave the redis cache intact if we want. This is helpful when, for
		 * instance, you're running a batch process and want to clear the session
		 * store to reduce the memory footprint, but you don't want to have to
		 * re-fetch all the values from the database.
		 *
		 * @param  bool $redis Should we flush redis as well as the session cache?
		 *
		 * @return bool Always returns true
		 */
		public function flush( $redis = true ) {

			$this->cache = [];

			if ( $redis ) {

				$this->_call_redis( $this->_create_container( 'flush' ) );

			}

			return true;

		}

		/**
		 * Retrieves the cache contents, if it exists
		 *
		 * The contents will be first attempted to be retrieved by searching by the
		 * key in the cache group. If the cache is hit (success) then the contents
		 * are returned.
		 *
		 * On failure, the number of cache misses will be incremented.
		 *
		 * @param int|string $key What the contents in the cache are called
		 * @param string $group Where the cache contents are grouped
		 * @param bool $force Whether to force a refetch rather than relying on the local cache (default is false)
		 * @param bool $found Optional. Whether the key was found in the cache. Disambiguates a return of false, a storable value. Passed by reference. Default null.
		 *
		 * @return bool|mixed False on failure to retrieve contents or the cache
		 *      contents on success
		 */
		public function get( $key, $group = 'default', $force = false, &$found = null ) {

			if ( empty( $group ) ) {

				$group = 'default';

			}

			// Key is set internally, so we can use this value
			if ( $this->_isset_internal( $key, $group ) && ! $force ) {

				$this->cache_hits += 1;

				$found = true;

				$value = $this->_get_internal( $key, $group );

				// Check notoptions is valid. Delete and return false if not
				if ( 'notoptions' === $key && ! $this->validate_notoptions( $value ) ) {

					$this->delete( $key, $group );

					return false;
				}

				return $value;
			}

			// Not a persistent group, so don't try Redis if the value doesn't exist internally
			if ( ! $this->_should_persist( $group ) ) {

				$this->cache_misses += 1;

				$found = false;

				return false;

			}

			$id = $this->_key( $key, $group );

			$value = $this->_call_redis( $this->_create_container( 'get', $id ) );

			// If the key does not exist, $value will be the boolean false.  Otherwise
			// it will contain our data.
			if ( false === $value ) {

				$this->cache_misses += 1;

				$found = false;

				return false;

			}

			// All non-numeric values are serialized
			if ( ! is_numeric( $value ) ) {
				$value = unserialize( base64_decode( $value ) );
			}

			// Check notoptions is valid. Delete and return false if not
			if ( 'notoptions' === $key && ! $this->validate_notoptions( $value ) ) {

				$this->delete( $key, $group );

				return false;
			}


			$this->_set_internal( $key, $group, $value );

			$this->cache_hits += 1;

			$found = true;

			return $value;

		}

		/**
		 * Increment numeric cache item's value
		 *
		 * @param int|string $key The cache key to increment
		 * @param int $offset The amount by which to increment the item's value. Default is 1.
		 * @param string $group The group the key is in.
		 *
		 * @return false|int False on failure, the item's new value on success.
		 */
		public function incr( $key, $offset = 1, $group = 'default' ) {

			if ( empty( $group ) ) {

				$group = 'default';

			}

			// The key needs to exist in order to be incremented
			if ( ! $this->_exists( $key, $group ) ) {

				return false;

			}

			$offset = (int) $offset;

			# If this isn't a persistant group, we have to sort this out ourselves, grumble grumble
			if ( ! $this->_should_persist( $group ) ) {

				$existing = $this->_get_internal( $key, $group );

				if ( empty( $existing ) || ! is_numeric( $existing ) ) {

					$existing = 1;

				} else {

					$existing += $offset;

				}

				if ( $existing < 0 ) {

					$existing = 0;

				}

				$this->_set_internal( $key, $group, $existing );

				return $existing;

			}

			$id = $this->_key( $key, $group );

			$result = $this->_call_redis( $this->_create_container( 'incrby', $id, $offset ) );

			if ( $result < 0 ) {

				$result = 0;

				$this->_call_redis( $this->_create_container( 'set', $id, $result ) );

			}

			if ( is_int( $result ) ) {

				$this->_set_internal( $key, $group, $result );

			}

			return $result;

		}

		/**
		 * Replace the contents in the cache, if contents already exist
		 * @see WP_Object_Cache::set()
		 *
		 * @param int|string $key What to call the contents in the cache
		 * @param mixed $data The contents to store in the cache
		 * @param string $group Where to group the cache contents
		 * @param int $expire When to expire the cache contents
		 *
		 * @return bool False if not exists, true if contents were replaced
		 */
		public function replace( $key, $data, $group = 'default', $expire = 0 ) {

			if ( empty( $group ) ) {

				$group = 'default';

			}

			if ( ! $this->_exists( $key, $group ) ) {

				return false;

			}

			return $this->set( $key, $data, $group, (int) $expire );

		}

		/**
		 * Reset keys
		 *
		 * @deprecated 3.5.0
		 */
		public function reset() {

			_deprecated_function( __FUNCTION__, '3.5', 'switch_to_blog()' );

		}

		/**
		 * Sets the data contents into the cache
		 *
		 * The cache contents is grouped by the $group parameter followed by the
		 * $key. This allows for duplicate ids in unique groups. Therefore, naming of
		 * the group should be used with care and should follow normal function
		 * naming guidelines outside of core WordPress usage.
		 *
		 * The $expire parameter is not used, because the cache will automatically
		 * expire for each time a page is accessed and PHP finishes. The method is
		 * more for cache plugins which use files.
		 *
		 * @param int|string $key What to call the contents in the cache
		 * @param mixed $data The contents to store in the cache
		 * @param string $group Where to group the cache contents
		 * @param int $expire TTL for the data, in seconds
		 *
		 * @return bool Always returns true
		 */
		public function set( $key, $data, $group = 'default', $expire = 0 ) {

			if ( empty( $group ) ) {

				$group = 'default';

			}

			if ( is_object( $data ) ) {

				$data = clone $data;

			}

			// Do not allow an invalid notoptions to be set
			if ( 'notoptions' === $key && ! $this->validate_notoptions( $data ) ) {

				return true;
			}

			$this->_set_internal( $key, $group, $data );

			if ( ! $this->_should_persist( $group ) ) {

				return true;

			}

			# If this is an integer, store it as such. Otherwise, serialize it.
			if ( ! is_numeric( $data ) || intval( $data ) !== $data ) {

				$data = base64_encode( serialize( $data ) );

			}

			$id = $this->_key( $key, $group );

			$this->_call_redis( $this->_create_container( 'set', $id, $data, $expire ) );

			return true;

		}

		/**
		 * Echoes the stats of the caching.
		 *
		 * Gives the cache hits, and cache misses. Also prints every cached group,
		 * key and the data.
		 */
		public function stats() {

			$total_redis_calls = 0;

			foreach ( $this->redis_calls as $method => $calls ) {

				$total_redis_calls += $calls;

			}

			$out   = [];
			$out[] = '<p>';
			$out[] = '<strong>Cache Hits:</strong>' . (int) $this->cache_hits . '<br />';
			$out[] = '<strong>Cache Misses:</strong>' . (int) $this->cache_misses . '<br />';
			$out[] = '<strong>Redis Calls:</strong>' . (int) $total_redis_calls . ':<br />';

			foreach ( $this->redis_calls as $method => $calls ) {

				$out[] = ' - ' . esc_html( $method ) . ': ' . (int) $calls . '<br />';

			}

			$out[] = '</p>';
			$out[] = '<ul>';

			foreach ( $this->cache as $group => $cache ) {

				$out[] = '<li><strong>Group:</strong> ' . esc_html( $group ) . ' - ( ' . number_format( mb_strlen( base64_encode( serialize( $cache ) ) ) / 1024, 2 ) . 'k )</li>';

			}

			$out[] = '</ul>';

			// @codingStandardsIgnoreStart
			echo implode( PHP_EOL, $out );
			// @codingStandardsIgnoreEnd

		}

		/**
		 * Switch the interal blog id.
		 *
		 * This changes the blog id used to create keys in blog specific groups.
		 *
		 * @param int $blog_id Blog ID
		 */
		public function switch_to_blog( $blog_id ) {

			$blog_id = (int) $blog_id;

			$this->blog_prefix = $this->multisite ? $blog_id . ':' : '';

		}

		/**
		 * Utility function to determine whether a key exists in the cache.
		 *
		 * @access protected
		 *
		 * @param $key
		 * @param $group
		 *
		 * @return bool
		 */
		protected function _exists( $key, $group ) {

			if ( $this->_isset_internal( $key, $group ) ) {

				return true;

			}

			if ( ! $this->_should_persist( $group ) ) {

				return false;

			}

			$id = $this->_key( $key, $group );

			return $this->_call_redis( $this->_create_container( 'exists', $id ) );

		}

		/**
		 * Check whether there's a value in the internal object cache.
		 *
		 * @param string $key
		 * @param string $group
		 *
		 * @return boolean
		 */
		protected function _isset_internal( $key, $group ) {

			$key = $this->_key( $key, $group );

			return isset( $this->cache[ $key ] );

		}

		/**
		 * Get a value from the internal object cache
		 *
		 * @param string $key
		 * @param string $group
		 *
		 * @return mixed
		 */
		protected function _get_internal( $key, $group ) {

			$value = null;

			$key = $this->_key( $key, $group );

			if ( isset( $this->cache[ $key ] ) ) {

				$value = $this->cache[ $key ];

			}

			if ( is_object( $value ) ) {

				return clone $value;

			}

			return $value;

		}

		/**
		 * Set a value to the internal object cache
		 *
		 * @param string $key
		 * @param string $group
		 * @param mixed $value
		 */
		protected function _set_internal( $key, $group, $value ) {

			// Redis converts null to an empty string
			if ( is_null( $value ) ) {

				$value = '';

			}

			$key = $this->_key( $key, $group );

			$this->cache[ $key ] = $value;

		}

		/**
		 * Unset a value from the internal object cache
		 *
		 * @param string $key
		 * @param string $group
		 */
		protected function _unset_internal( $key, $group ) {

			$key = $this->_key( $key, $group );

			if ( isset( $this->cache[ $key ] ) ) {

				unset( $this->cache[ $key ] );
			}

		}

		/**
		 * Utility function to generate the redis key for a given key and group.
		 *
		 * @param  string $key The cache key.
		 * @param  string $group The cache group.
		 *
		 * @return string        A properly prefixed redis cache key.
		 */
		protected function _key( $key = '', $group = 'default' ) {

			if ( empty( $group ) ) {

				$group = 'default';

			}

			if ( ! empty( $this->global_groups[ $group ] ) ) {

				$prefix = $this->global_prefix;

			} else {

				$prefix = $this->blog_prefix;

			}

			return preg_replace( '/\s+/', '', WP_CACHE_KEY_SALT . "$prefix$group:$key" );

		}

		/**
		 * Does this group use persistent storage?
		 *
		 * @param  string $group Cache group.
		 *
		 * @return bool        true if the group is persistent, false if not.
		 */
		protected function _should_persist( $group ) {

			return empty( $this->non_persistent_groups[ $group ] );

		}

		/**
		 * Wrapper method for connecting to Redis, which lets us retry the connection
		 */
		protected function _connect_redis() {

			global $redis_socket_file;

			if ( empty( $redis_socket_file ) ) {

				if ( defined( 'REDIS_SOCKET' ) ) {

					$redis_socket_file = REDIS_SOCKET;

				} else {

					$this->missing_redis_message = 'Warning! No socket configured.  This object cache will not work without a defined REDIS_SOCKET';

					return false;

				}

			}

			$this->redis_proxy = @stream_socket_client( 'unix://' . $redis_socket_file, $errno, $errstr, 5 );

			@stream_set_blocking( $this->redis_proxy, false );
			@stream_set_timeout( $this->redis_proxy, 5 );

			if ( ! $this->redis_proxy ) {

				$this->missing_redis_message = "Error [{$errno}] connecting to the Redis Proxy:\n{$errstr}";

				$this->is_redis_connected = false;

			} else {

				$this->is_redis_connected = true;

			}

			return $this->is_redis_connected;

		}

		/**
		 * Helper method, to write out to our socket, and make sure all the data
		 * is written out.
		 *
		 * @param string $string The string that we want to send to our socket
		 *
		 * @return int           The number of bytes successfully written to the socket
		 */
		function fwrite_stream( $string ) {

			$to_write = mb_strlen( $string );

			$info = stream_get_meta_data( $this->redis_proxy );

			for ( $written = 0; $written < $to_write; $written += $fwrite ) {

				// PHP throws a Notice if we are not able to write the entire $string in
				// one attempt.  Suppress notices here, and we can detect issues if any
				// occur up stream.
				$fwrite = @fwrite( $this->redis_proxy, substr( $string, $written ) );
				$fflush = fflush( $this->redis_proxy );

				if ( false === $fwrite ) {

					return $written;

				}

				if ( false === $fflush ) {

					return $written;

				}

				if ( $info['timed_out'] ) {

					return $written;

				}

			}

			return $written;

		}

		/**
		 * Wrapper method for calls to Redis, which fails gracefully when Redis is unavailable
		 *
		 * @param string $container Encoded message to send to Redis
		 *
		 * @return mixed $retval    Response received from Redis
		 * @throws Exception
		 */
		protected function _call_redis( $container ) {

			$retval = false;

			if ( $this->is_redis_connected && ! empty( $container ) ) {

				try {
					// we should write all the data in $json to the socket.  This will
					// check that we do, prior to attempting to get a response.
					if ( mb_strlen( $container ) == $this->fwrite_stream( $container ) ) {

						//contents of $response should be:
						// $response['response'] = true|false
						// $response['data'] = 'returned data'
						$retval = $this->_deconst_container( $this->get_response() );
					}

					return $retval;

				} catch ( Exception $e ) {

					// PhpRedis throws an Exception when it fails a server call.
					// To prevent WordPress from fataling, we catch the Exception.
					// TODO: We may need to tweak these messages that we filter on.
					$retry_exception_messages = [
						'socket error on read socket',
						'Connection closed',
						'Redis server went away'
					];
					$retry_exception_messages = apply_filters( 'wp_redis_retry_exception_messages', $retry_exception_messages );

					if ( in_array( $e->getMessage(), $retry_exception_messages, true ) ) {

						try {

							$this->last_triggered_error = 'WP Redis: ' . $e->getMessage();

							// Be friendly to developers debugging production servers by triggering an error
							// @codingStandardsIgnoreStart
							trigger_error( $this->last_triggered_error, E_USER_WARNING );
							// @codingStandardsIgnoreEnd

						} catch ( PHPUnit_Framework_Error_Warning $e ) {

							// PHPUnit throws an Exception when `trigger_error()` is called.
							// To ensure our tests (which expect Exceptions to be caught) continue to run,
							// we catch the PHPUnit exception and inspect the RedisException message

						}

						// Attempt to refresh the connection if it was successfully established once
						// $this->is_redis_connected will be set inside _connect_redis()
						if ( $this->_connect_redis() ) {

							return call_user_func_array( [ $this, '_call_redis' ], $container );

						}

						// Fall through to fallback below
					} else {

						throw $e;

					}

				}

			}

		}


		/**
		 * Helper function. Strips the size value from the stream, then returns the actual json response
		 */
		protected function get_response() {
			$resp     = '';
			$get_size = true;
			$str      = '';
			$start    = time();

			if ( $this->redis_proxy ) {

				// The minimum size to get is 29, which corresponds to a true response. Start there, then read
				// more if need be
				$bytes_to_read = $this->min_read_size;

				do {

					$data = fread( $this->redis_proxy, $bytes_to_read );

					if ( false === $data ) {

						$bytes_to_read = 0;

					} else {

						if ( $get_size ) {

							$str .= $data;

							if ( preg_match( '/size: (\d+)\n(.*)/', $str, $matches ) ) {

								$resp          = $matches[2];
								$bytes_to_read = $matches[1] - mb_strlen( $resp );
								$get_size      = false;

							}

						} else {

							$bytes_to_read -= mb_strlen( $data );

							$resp .= $data;

						}

					}

					if ( time() > $start + $this->max_read_time ) {

						$bytes_to_read = 0;
						$resp          = false;

					}

				} while ( $bytes_to_read > 0 );

			}

			return $resp;

		}

		/**
		 * Admin UI to let the end user know something about the Redis connection isn't working.
		 */
		public function wp_action_admin_notices_warn_missing_redis() {

			if ( ! current_user_can( 'manage_options' ) || empty( $this->missing_redis_message ) ) {

				return;

			}

			echo '<div class="message error"><p>' . esc_html( $this->missing_redis_message ) . '</p></div>';

		}

		/**
		 * Returns the XID of the account.
		 *
		 * @return string $xid currnet account xid.
		 */
		protected function _get_xid() {

			// Get the xid from the path.
			$path = __DIR__;

			if ( empty( $path ) ) {

				return false;

			}

			$path_portions = explode( '/', $path );
			$length        = count( $path_portions );

			// XID is the parent directory of the root base directory 'html'.
			// Need to remove 2 get the correct position.  array[0] is blank
			$xid_pos = $length - 3;

			if ( is_numeric( $path_portions[ $xid_pos ] ) ) {

				// XID from path on servers other than SFTP.
				return $path_portions[ $xid_pos ];

			}

			// XID from path on SFTP servers.
			return substr( substr( $path_portions[ $xid_pos ], 4 ), 0, - 3 );

		}


		/**
		 * Handler packages cache requests into container to be parsed by the Redis client.
		 *
		 * @param string $action sets container content and format based on class method calling the handler.
		 * @param bool|string $key the group and key for the cache data concatenated together.(default is false)
		 * @param bool|string $data the data to be cached (default is false)
		 * @param int $expire setting for cache expiration (default = default_expire).
		 *
		 * @return string $container
		 */
		protected function _create_container( $action, $key = false, $data = false, $expire = 0 ) {

			if ( empty( $action ) ) {

				return false;

			}

			$xid = $this->_get_xid();

			// If $xid is false do not return a container.
			if ( false === $xid ) {

				return false;

			}

			// given the value of $action build and format the array for the corresponding method.
			switch ( $action ) {

				case 'del':

					$container = [ 'action' => 'del', 'xid' => $xid, 'key' => $key ];

					break;

				case 'flush':

					$container = [ 'action' => 'flush', 'xid' => $xid ];

					break;

				case 'get':

					$container = [ 'action' => 'get', 'xid' => $xid, 'key' => $key ];

					break;

				case 'incrby':

					$container = [ 'action' => 'incrby', 'xid' => $xid, 'key' => $key, 'offset' => $data ];

					break;

				case 'exists':

					$container = [ 'action' => 'exists', 'xid' => $xid, 'key' => $key ];

					break;

				case 'set':

					if ( 00 == $expire ) {

						$expire = $this->default_expire;

					} else if ( $expire > $this->max_cache_ttl ) {

						$expire = $this->max_cache_ttl;

					}

					$expire    = ( time() + $expire ) * 1000;
					$container = [
						'action' => 'set',
						'xid'    => $xid,
						'key'    => $key,
						'data'   => (string) $data,
						'expire' => (string) $expire
					];

					break;

				default:

					return false;

					break;

			}
			// create json container from array.
			$json_container = json_encode( $container );

			// Get length of json encoded string.
			$size = mb_strlen( $json_container . "\n" );

			// Add string length to the json blob.
			$container = 'size: ' . $size . "\n" . $json_container . "\n";

			return $container;

		}

		/**
		 * Handler to remove cached data from JSON container returned from Redis.
		 *
		 * @param string $response response from Redis client to be opened and parsed.
		 *
		 * @return mixed return if data exists return true and data as string else return false.
		 * @throws Exception
		 */
		protected function _deconst_container( $response ) {

			if ( empty( $response ) ) {

				return false;

			}

			// Convert JSON to php object.
			try {

				$response_obj = json_decode( $response );

				if ( 'false' == strtolower( $response_obj->response ) ) {

					return false;

				}

				return isset( $response_obj->data ) ? $response_obj->data : false;

			} catch ( Exception $e ) {

				throw new Exception( 'JSON Error: ' . json_last_error_msg() );

			}

		}

		/**
		 * Will validate that nonoptions contains correct data
		 *
		 * Called when we get/set nonoptions key
		 *
		 * @return bool True is valid, false if invalid
		 */
		protected function validate_notoptions( $value ) {
			// If this is not an array, then it is not valid
			if ( ! is_array( $value ) ) {

				return false;

			}

			// Check that this contains boolean values (or at least the first element)
			reset( $value );
			$first_key = key( $value );

			if ( ! is_bool( $value[ $first_key ] ) ) {

				return false;
			}

			// Check for values WordPress has to have in order to work
			if ( array_key_exists( 'site_url', $value ) || array_key_exists( 'template', $value ) ) {
				return false;
			}

			return true;
		}

		/**
		 * Sets up object properties; PHP 5 style constructor
		 *
		 * @return null|WP_Object_Cache If cache is disabled, returns null.
		 */
		public function __construct() {

			global $blog_id, $table_prefix;

			$this->multisite   = is_multisite();
			$this->blog_prefix = $this->multisite ? $blog_id . ':' : '';

			if ( ! $this->_connect_redis() && function_exists( 'add_action' ) ) {

				add_action( 'admin_notices', [ $this, 'wp_action_admin_notices_warn_missing_redis' ] );

			}

			$this->global_prefix = ( $this->multisite || defined( 'CUSTOM_USER_TABLE' ) && defined( 'CUSTOM_USER_META_TABLE' ) ) ? '' : $table_prefix;

		}

		/**
		 * Will save the object cache before object is completely destroyed.
		 *
		 * Called upon object destruction, which should be when PHP ends.
		 *
		 * @return bool True value. Won't be used by PHP
		 */
		public function __destruct() {

			return true;

		}
	}


	class APCu_Object_Cache {

		private $prefix = '';
		private $local_cache = array();
		private $global_groups = array();
		private $non_persistent_groups = array();
		private $multisite = false;
		private $blog_prefix = '';

		public function __construct() {
			global $table_prefix;

			$this->multisite   = is_multisite();
			$this->blog_prefix = $this->multisite ? get_current_blog_id() . ':' : '';
			$this->prefix      = DB_HOST . '.' . DB_NAME . '.' . $table_prefix;
		}

		private function get_group( $group ) {
			return empty( $group ) ? 'default' : $group;
		}

		private function get_key( $group, $key ) {
			if ( $this->multisite && ! isset( $this->global_groups[ $group ] ) ) {
				return $this->prefix . '.' . $group . '.' . $this->blog_prefix . ':' . $key;
			} else {
				return $this->prefix . '.' . $group . '.' . $key;
			}
		}

		public function add( $key, $data, $group = 'default', $expire = 0 ) {
			$group = $this->get_group( $group );
			$key   = $this->get_key( $group, $key );

			if ( function_exists( 'wp_suspend_cache_addition' ) && wp_suspend_cache_addition() ) {
				return false;
			}
			if ( isset( $this->local_cache[ $group ][ $key ] ) ) {
				return false;
			}
			// FIXME: Somehow apcu_add does not return false if key already exists
			if ( ! isset( $this->non_persistent_groups[ $group ] ) && apcu_exists( $key ) ) {
				return false;
			}

			if ( is_object( $data ) ) {
				$this->local_cache[ $group ][ $key ] = clone $data;
			} else {
				$this->local_cache[ $group ][ $key ] = $data;
			}

			if ( ! isset( $this->non_persistent_groups[ $group ] ) ) {
				return apcu_add( $key, $data, (int) $expire );
			}

			return true;
		}

		public function add_global_groups( $groups ) {
			if ( is_array( $groups ) ) {
				foreach ( $groups as $group ) {
					$this->global_groups[ $group ] = true;
				}
			} else {
				$this->global_groups[ $groups ] = true;
			}
		}

		public function add_non_persistent_groups( $groups ) {
			if ( is_array( $groups ) ) {
				foreach ( $groups as $group ) {
					$this->non_persistent_groups[ $group ] = true;
				}
			} else {
				$this->non_persistent_groups[ $groups ] = true;
			}
		}

		public function decr( $key, $offset = 1, $group = 'default' ) {
			if ( $offset < 0 ) {
				return $this->incr( $key, abs( $offset ), $group );
			}

			$group = $this->get_group( $group );
			$key   = $this->get_key( $group, $key );

			if ( isset( $this->local_cache[ $group ][ $key ] ) && $this->local_cache[ $group ][ $key ] - $offset >= 0 ) {
				$this->local_cache[ $group ][ $key ] -= $offset;
			} else {
				$this->local_cache[ $group ][ $key ] = 0;
			}

			if ( isset( $this->non_persistent_groups[ $group ] ) ) {
				return $this->local_cache[ $group ][ $key ];
			} else {
				$value = apcu_dec( $key, $offset );
				if ( $value < 0 ) {
					apcu_store( $key, 0 );

					return 0;
				}

				return $value;
			}
		}

		public function delete( $key, $group = 'default', $force = false ) {
			$group = $this->get_group( $group );
			$key   = $this->get_key( $group, $key );

			unset( $this->local_cache[ $group ][ $key ] );
			if ( ! isset( $this->non_persistent_groups[ $group ] ) ) {
				return apcu_delete( $key );
			}

			return true;
		}

		public function flush() {
			$this->local_cache = array();
			// TODO: only clear our own entries
			apcu_clear_cache();

			return true;
		}

		public function get( $key, $group = 'default', $force = false, &$found = null ) {
			$group = $this->get_group( $group );
			$key   = $this->get_key( $group, $key );

			if ( ! $force && isset( $this->local_cache[ $group ][ $key ] ) ) {
				$found = true;
				if ( is_object( $this->local_cache[ $group ][ $key ] ) ) {
					return clone $this->local_cache[ $group ][ $key ];
				} else {
					return $this->local_cache[ $group ][ $key ];
				}
			} elseif ( isset( $this->non_persistent_groups[ $group ] ) ) {
				$found = false;

				return false;
			} else {
				$value = apcu_fetch( $key, $found );
				if ( $found ) {
					if ( $force ) {
						$this->local_cache[ $group ][ $key ] = $value;
					}

					return $value;
				} else {
					return false;
				}
			}
		}

		public function incr( $key, $offset = 1, $group = 'default' ) {
			if ( $offset < 0 ) {
				return $this->decr( $key, abs( $offset ), $group );
			}

			$group = $this->get_group( $group );
			$key   = $this->get_key( $group, $key );

			if ( isset( $this->local_cache[ $group ][ $key ] ) && $this->local_cache[ $group ][ $key ] + $offset >= 0 ) {
				$this->local_cache[ $group ][ $key ] += $offset;
			} else {
				$this->local_cache[ $group ][ $key ] = 0;
			}

			if ( isset( $this->non_persistent_groups[ $group ] ) ) {
				return $this->local_cache[ $group ][ $key ];
			} else {
				$value = apcu_inc( $key, $offset );
				if ( $value < 0 ) {
					apcu_store( $key, 0 );

					return 0;
				}

				return $value;
			}
		}

		public function replace( $key, $data, $group = 'default', $expire = 0 ) {
			$group = $this->get_group( $group );
			$key   = $this->get_key( $group, $key );

			if ( isset( $this->non_persistent_groups[ $group ] ) ) {
				if ( ! isset( $this->local_cache[ $group ][ $key ] ) ) {
					return false;
				}
			} else {
				if ( ! isset( $this->local_cache[ $group ][ $key ] ) && ! apcu_exists( $key ) ) {
					return false;
				}
				apcu_store( $key, $data, (int) $expire );
			}

			if ( is_object( $data ) ) {
				$this->local_cache[ $group ][ $key ] = clone $data;
			} else {
				$this->local_cache[ $group ][ $key ] = $data;
			}

			return true;
		}

		public function reset() {
			// This function is deprecated as of WordPress 3.5
			// Be safe and flush the cache if this function is still used
			$this->flush();
		}

		public function set( $key, $data, $group = 'default', $expire = 0 ) {
			$group = $this->get_group( $group );
			$key   = $this->get_key( $group, $key );

			if ( is_object( $data ) ) {
				$this->local_cache[ $group ][ $key ] = clone $data;
			} else {
				$this->local_cache[ $group ][ $key ] = $data;
			}

			if ( ! isset( $this->non_persistent_groups[ $group ] ) ) {
				return apcu_store( $key, $data, (int) $expire );
			}

			return true;
		}

		public function stats() {
			// Only implemented because the default cache class provides this.
			// This method is never called.
			echo '';
		}

		public function switch_to_blog( $blog_id ) {
			$this->blog_prefix = $this->multisite ? $blog_id . ':' : '';
		}

	}

else:

	wp_using_ext_object_cache( false );

	require_once( ABSPATH . WPINC . '/cache.php' );

endif;