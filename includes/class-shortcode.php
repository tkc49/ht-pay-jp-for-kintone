<?php
/**
 * ShortCode
 *
 * @package Payjp_For_Kintone
 */

/**
 * HT PAY.JP
 */
class Shortcode {


	/**
	 * Constructor.
	 */
	public function __construct() {

		// PAY.JP checkoutコードを表示させるオリジナルタグを追加.
		add_action( 'wpcf7_init', array( $this, 'payjpforkintone_add_form_tag' ) );

		// Payment.
		require_once PAY_JP_FOR_KINTONE_PATH . '/includes/class-payment.php';
		new Payment();

	}

	/**
	 * PAY.JP checkoutコードを表示させるオリジナルタグを追加.
	 */
	public function payjpforkintone_add_form_tag() {

		wpcf7_add_form_tag( 'payjp_for_kintone',
			array( $this, 'payjp_for_kintone_add_form_handler' ),
			array(
				'name-attr' => true,
			) );
	}

	public function payjp_for_kintone_add_form_handler( $tag ) {

		$contact_form                 = WPCF7_ContactForm::get_current();
		$payjpforkintone_setting_data = get_post_meta( $contact_form->id(), '_payjpforkintone_setting_data', true );

		if ( 'enable' !== $payjpforkintone_setting_data['payjpforkintone-enabled'] ) {
			return;
		}

		if ( isset( $payjpforkintone_setting_data['live-enabled'] ) && 'enable' === $payjpforkintone_setting_data['live-enabled'] ) {
			// Live.
			$public_key = get_option( 'pay_jp_for_kintone_live_public_key' );
		} else {
			$public_key = get_option( 'pay_jp_for_kintone_test_public_key' );
		}


		$html = '';
		$html .= '<script type="text/javascript" src="https://checkout.pay.jp/" class="payjp-button" data-key="' . $public_key . '" data-partial="true" ></script > ';

		return $html;

	}
}
