<?php

/**
 * Helper class for modifying URLs pointing back to the
 * main Beaver Builder website with referral data.
 *
 * @since 1.0.3
 */
final class FLBuilderBoosterURLs {

	/**
	 * @since 1.0.3
	 * @var string $id
	 */
	private $id = '864';

	/**
	 * FLBuilderBoosterURLs constructor.
	 *
	 * @since 1.0.3
	 */
	public function __construct() {

		add_filter( 'fl_builder_store_url', array( $this, 'modify_url' ), 999 );
		add_filter( 'all_plugins',          array( $this, 'modify_plugins_page' ) );
		add_filter( 'plugins_api_result',   array( $this, 'modify_plugins_info_popup' ) );
	}

	/**
	 * Modifies the builder store URL with referral data.
	 *
	 * @since 1.0.3
	 * @private
	 * @param string $url
	 * @return string
	 */
	public function modify_url( $url ) {
		
		$url = str_replace( array( 'utm_medium=bb-lite', 'utm_medium=bb-pro' ), 'utm_medium=bb-gd', $url );
		
		return add_query_arg( 'fla', $this->id, $url );
	}

	/**
	 * Modifies the plugin page.
	 *
	 * @since 1.0.3
	 * @private
	 * @param array $plugins
	 * @return string
	 */
	public function modify_plugins_page( $plugins ) {
		
		$key = 'beaver-builder-lite-version/fl-builder.php';
			
		if ( isset( $plugins[ $key ] ) ) {
			$plugins[ $key ]['PluginURI'] = add_query_arg( 'fla', $this->id, $plugins[ $key ]['PluginURI'] );
			$plugins[ $key ]['PluginURI'] = str_replace( 'utm_medium=bb', 'utm_medium=bb-gd', $plugins[ $key ]['PluginURI'] );
			$plugins[ $key ]['AuthorURI'] = add_query_arg( 'fla', $this->id, $plugins[ $key ]['AuthorURI'] );
			$plugins[ $key ]['AuthorURI'] = str_replace( 'utm_medium=bb', 'utm_medium=bb-gd', $plugins[ $key ]['AuthorURI'] );
		}

		return $plugins;
	}

	/**
	 * Modifies the plugin info popup.
	 *
	 * @since 1.0.3
	 * @private
	 * @param object $result
	 * @return string
	 */
	public function modify_plugins_info_popup( $result ) {
		
		if ( isset( $result->slug ) && 'beaver-builder-lite-version' == $result->slug ) {
			
			$search = array( '?utm_medium=bb-lite', '?utm_medium=bb' );
			$replace = '?fla=' . $this->id . '&#038;utm_medium=bb-gd';
			
			$result->author = str_replace( $search, $replace, $result->author );
			$result->homepage = str_replace( $search, $replace, $result->homepage );
			
			foreach ( $result->sections as $section => $content ) {
				$result->sections[ $section ] = str_replace( $search, $replace, $content );
			}
		}

		return $result;
	}
}

new FLBuilderBoosterURLs;
