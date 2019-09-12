<?php
/**
 * HT_Payjp_For_Kintone
 *
 * @package HT_Payjp_For_Kintone
 */

/**
 * HT_Payjp_For_Kintone
 */
class HT_Payjp_For_Kintone {

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
			require_once HT_PAY_JP_FOR_KINTONE_PATH . '/includes/class-ht-payjp-for-kintone-Admin.php';
			new HT_Payjp_For_Kintone_Admin();
		} else {
			require_once HT_PAY_JP_FOR_KINTONE_PATH . '/includes/class-ht-payjp-for-kintone-shortcode.php';
			new HT_Payjp_For_Kintone_Shortcode();
		}

	}

}
