<?php
/**
 * Payjp_For_Kintone
 *
 * @package Payjp_For_Kintone
 */

/**
 * Payjp_For_Kintone
 */
class Payjp_For_Kintone {

	/**
	 * Constructor
	 */
	public function __construct() {
	}

	/**
	 * Register
	 */
	public function register() {
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ), 1 );
	}

	/**
	 * Plugins Loaded
	 */
	public function plugins_loaded() {

		if ( ( is_admin() ) ) {
			require_once PAY_JP_FOR_KINTONE_PATH . '/includes/class-admin.php';
			new Admin();
		} else {
			require_once PAY_JP_FOR_KINTONE_PATH . '/includes/class-shortcode.php';
			new Shortcode();
		}

	}

}
