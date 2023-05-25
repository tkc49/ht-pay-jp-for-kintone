<?php
/**
 * ShortCode
 *
 * @package Payjp_For_Kintone
 */

/**
 * HT PAY.JP
 */
class HT_Payjp_For_Kintone_Shortcode {


	/**
	 * Constructor.
	 */
	public function __construct() {

		// PAY.JP checkoutコードを表示させるオリジナルタグを追加.
		add_action( 'wpcf7_init', array( $this, 'ht_payjpforkintone_add_form_tag' ) );

		// Payment.
		require_once HT_PAY_JP_FOR_KINTONE_PATH . '/includes/class-ht-payjp-for-kintone-Payment.php';
		new HT_Payjp_For_Kintone_Payment();

	}

	/**
	 * PAY.JP checkoutコードを表示させるオリジナルタグを追加.
	 */
	public function ht_payjpforkintone_add_form_tag() {

		wpcf7_add_form_tag(
			'ht_payjp_for_kintone',
			array( $this, 'ht_payjp_for_kintone_add_form_handler' ),
			false
		);
	}

	/**
	 * CF7用のオリジナルタグを追加.
	 *
	 * @param WPCF7_FormTag $tag .
	 *
	 * @return string .
	 */
	public function ht_payjp_for_kintone_add_form_handler( $tag ) {

		$contact_form                 = WPCF7_ContactForm::get_current();
		$payjpforkintone_setting_data = get_post_meta( $contact_form->id(), '_ht_payjpforkintone_setting_data', true );

		if ( 'enable' !== $payjpforkintone_setting_data['payjpforkintone-enabled'] ) {
			return '';
		}

		if ( isset( $payjpforkintone_setting_data['live-enabled'] ) && 'enable' === $payjpforkintone_setting_data['live-enabled'] ) {
			// Live.
			$public_key = get_option( 'ht_pay_jp_for_kintone_live_public_key' );
		} else {
			$public_key = get_option( 'ht_pay_jp_for_kintone_test_public_key' );
		}

		$payjpforkintone_language = 'ja';
		if ( isset( $payjpforkintone_setting_data['payjpforkintone-language'] )) {
			$payjpforkintone_language= $payjpforkintone_setting_data['payjpforkintone-language'];
		}

		$html = '<script type="text/javascript" src="https://checkout.pay.jp/" class="payjp-button" data-key="' . $public_key . '" data-partial="true" data-lang="'.$payjpforkintone_language.'" ></script > ';

		return $html;

	}
}
