<?php

/**
 * Load required classes
 *
 * @package   WCPOS\Start
 * @author    Paul Kilmurray <paul@kilbot.com.au>
 * @link      http://www.wcpos.com
 */

namespace WCPOS;

class Start {

	/**
	 * Constructor
	 */
	public function __construct() {
		// global helper functions
		require_once PLUGIN_PATH . 'includes/wc-pos-functions.php';

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'rest_api_init', array( $this, 'rest_api_init' ), 20 );
		add_filter( 'http_request_args', array( $this, 'http_request_args' ), 10, 2 );
	}

	/**
	 * Load the required resources
	 */
	public function init() {
		// common classes
		new i18n();
		new Gateways();
		new Products();
		new Customers();

		// ajax only
		if ( is_admin() && ( defined( '\DOING_AJAX' ) && \DOING_AJAX ) ) {
			new AJAX();
		}

		// admin only
		if ( is_admin() && ! ( defined( '\DOING_AJAX' ) && \DOING_AJAX ) ) {
			new Admin();
		} // frontend only
		else {
			new Template();
		}

		// load integrations
		$this->integrations();

	}


	/**
	 * Loads the POS API and patches to the WC REST API
	 */
	public function rest_api_init() {
		if ( is_pos() ) {
			new API();
		}
	}


	/**
	 * Loads POS integrations with third party plugins
	 */
	private function integrations() {
		// WooCommerce Bookings - http://www.woothemes.com/products/woocommerce-bookings/
		if ( class_exists( 'WC-Bookings' ) ) {
			new Integrations\Bookings();
		}
	}

	public function http_request_args( $r, $url ) {
		if ( is_pos() ) {
			$headers      = array(
				'X-WC-POS' => 1
			);
			$r['headers'] = is_array( $r['headers'] ) ? array_merge( $r['headers'], $headers ) : $headers;
			// self-signed certificates
			if ( $url == rest_url( 'wcpos/v1/user' ) ) {
				$r['sslverify'] = false;
			}
		}

		return $r;
	}
}
