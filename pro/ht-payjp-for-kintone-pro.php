<?php
if ( ! class_exists( 'ht_payjp_for_kintone_pro' ) ):
	class ht_payjp_for_kintone_pro {

		/*
		*  __construct
		*/


		function __construct() {

			// constants
			add_action( 'init', array( $this, 'register_assets' ) );

			if ( ( is_admin() ) ) {
				require_once HT_PAY_JP_FOR_KINTONE_PATH . 'pro/includes/class-ht-payjp-for-kintone-pro-admin.php';
				new HT_Payjp_For_Kintone_Pro_Admin();
			} else {
				require_once HT_PAY_JP_FOR_KINTONE_PATH . 'pro/includes/class-ht-payjp-for-kintone-pro-subscription.php';
				new HT_Payjp_For_Kintone_Pro_Subscription();

				require_once HT_PAY_JP_FOR_KINTONE_PATH . 'pro/includes/rest-api/billing.php';
			}
		}


		/*
		*  register_assets
		*
		*  description
		*
		*  @type	function
		*  @date	4/11/2013
		*  @since	5.0.0
		*
		*  @param	$post_id (int)
		*  @return	$post_id (int)
		*/

		function register_assets() {

			// register scripts
			wp_register_script(
				'ht-payjp-for-kintone-pro',
				HT_PAY_JP_FOR_KINTONE_URL . '/pro/assets/js/ht-payjp-for-kintone-pro.js',
				array( 'payjpforkintone-main-js' ),
				date(
					'YmdGis',
					filemtime( HT_PAY_JP_FOR_KINTONE_PATH . '/pro/assets/js/ht-payjp-for-kintone-pro.js' )
				),
				true
			);

		}


		/*
		*  admin_enqueue_scripts
		*/
		function admin_enqueue_scripts() {

			wp_enqueue_script( 'ht-payjp-for-kintone-pro' );

		}


	}

	new ht_payjp_for_kintone_pro();
endif;
