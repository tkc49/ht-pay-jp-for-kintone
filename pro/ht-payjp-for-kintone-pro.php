<?php
if ( ! class_exists( 'ht_payjp_for_kintone_pro' ) ):
	class ht_payjp_for_kintone_pro {

		/*
		*  __construct
		*/


		function __construct() {

			// constants
//			acf()->define( 'ACF_PRO', true );

			// update setting
//			acf_update_setting( 'pro', true );
//			acf_update_setting( 'name', __( 'Advanced Custom Fields PRO', 'acf' ) );

			// includes
//			acf_include( 'pro/blocks.php' );
//			acf_include( 'pro/options-page.php' );
//			acf_include( 'pro/updates.php' );

//			if ( is_admin() ) {
//
//				acf_include( 'pro/admin/admin-options-page.php' );
//				acf_include( 'pro/admin/admin-updates.php' );
//
//			}

			add_action( 'init', array( $this, 'register_assets' ) );

			if ( ( is_admin() ) ) {
				require_once HT_PAY_JP_FOR_KINTONE_PATH . 'pro/includes/class-ht-payjp-for-kintone-pro-admin.php';
				new HT_Payjp_For_Kintone_Pro_Admin();
			} else {
				require_once HT_PAY_JP_FOR_KINTONE_PATH . 'pro/includes/class-ht-payjp-for-kintone-pro-subscription.php';
				new HT_Payjp_For_Kintone_Pro_Subscription();
			}


//			add_action( 'acf/include_field_types', array( $this, 'include_field_types' ), 5 );
//			add_action( 'acf/include_location_rules', array( $this, 'include_location_rules' ), 5 );

//			add_action( 'acf/field_group/admin_enqueue_scripts', array( $this, 'field_group_admin_enqueue_scripts' ) );

		}


		/*
		*  include_field_types
		*
		*  description
		*
		*  @type	function
		*  @date	21/10/2015
		*  @since	5.2.3
		*
		*  @param	$post_id (int)
		*  @return	$post_id (int)
		*/

		function include_field_types() {

			acf_include( 'pro/fields/class-acf-field-repeater.php' );
			acf_include( 'pro/fields/class-acf-field-flexible-content.php' );
			acf_include( 'pro/fields/class-acf-field-gallery.php' );
			acf_include( 'pro/fields/class-acf-field-clone.php' );

		}


		/*
		*  include_location_rules
		*
		*  description
		*
		*  @type	function
		*  @date	10/6/17
		*  @since	5.6.0
		*
		*  @param	$post_id (int)
		*  @return	$post_id (int)
		*/

		function include_location_rules() {

			acf_include( 'pro/locations/class-acf-location-block.php' );
			acf_include( 'pro/locations/class-acf-location-options-page.php' );

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

			// vars
//			$version = acf_get_setting( 'version' );
//			$min     = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

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
//			wp_register_script( 'acf-pro-field-group', acf_get_url( "pro/assets/js/acf-pro-field-group{$min}.js" ), array( 'acf-field-group' ), $version );

			// register styles
//			wp_register_style( 'acf-pro-input', acf_get_url( 'pro/assets/css/acf-pro-input.css' ), array( 'acf-input' ), $version );
//			wp_register_style( 'acf-pro-field-group', acf_get_url( 'pro/assets/css/acf-pro-field-group.css' ), array( 'acf-input' ), $version );

		}


		/*
		*  admin_enqueue_scripts
		*/
		function admin_enqueue_scripts() {

			wp_enqueue_script( 'ht-payjp-for-kintone-pro' );
//			wp_enqueue_style( 'acf-pro-input' );

		}


		/*
		*  field_group_admin_enqueue_scripts
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

		function field_group_admin_enqueue_scripts() {

			wp_enqueue_script( 'acf-pro-field-group' );
			wp_enqueue_style( 'acf-pro-field-group' );

		}

	}

	new ht_payjp_for_kintone_pro();
endif;
?>
