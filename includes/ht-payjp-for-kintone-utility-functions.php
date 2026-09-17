<?php
function ht_payjp_for_kintone_get_path( $filename = '' ) {
	return HT_PAY_JP_FOR_KINTONE_PATH . ltrim( $filename, '/' );
}

function ht_payjp_for_kintone_include( $filename = '' ) {
	$file_path = ht_payjp_for_kintone_get_path( $filename );
	if ( file_exists( $file_path ) ) {
		include_once $file_path;
	}
}

function ht_payjp_for_kintone_send_error_mail( $contact_form, $erro_message ) {

	$kintone_setting_data = $contact_form->prop( 'kintone_setting_data' );

	if ( empty( $kintone_setting_data ) ) {
		return;
	}

	$email_address_to_send_kintone_registration_error = $kintone_setting_data['email_address_to_send_kintone_registration_error'];

	if ( $email_address_to_send_kintone_registration_error ) {
		$to = $email_address_to_send_kintone_registration_error;
	} else {
		$to = get_option( 'admin_email' );
	}

	$subject = esc_html__( 'Error : PAY.JP Payment', 'ht-pay-jp-for-kintone' );
	$body    = $erro_message;
	wp_mail( $to, $subject, $body );
}

/**
 * フォームに紐づく PAY.JP 設定を取得する.
 *
 * 決済設定を一度も保存していないフォームでは get_post_meta() が空文字列を返す。
 * そのまま配列アクセスすると PHP 8 で TypeError になり CF7 の送信が失敗するため、
 * 常に配列を返して呼び出し側の配列アクセスを安全にする.
 *
 * @param int $contact_form_id Contact Form 7 の投稿ID.
 *
 * @return array PAY.JP 設定. 未設定の場合は空配列.
 */
function ht_payjp_for_kintone_get_setting_data( $contact_form_id ) {

	$payjpforkintone_setting_data = get_post_meta(
		$contact_form_id,
		'_ht_payjpforkintone_setting_data',
		true
	);

	if ( ! is_array( $payjpforkintone_setting_data ) ) {
		return array();
	}

	return $payjpforkintone_setting_data;
}

function ht_payjp_for_kintone_get_api_key( $contact_form_id ) {

	if ( empty( $contact_form_id ) ) {
		$contact_form    = WPCF7_ContactForm::get_current();
		$contact_form_id = $contact_form->id();
	}

	$payjpforkintone_setting_data = ht_payjp_for_kintone_get_setting_data( $contact_form_id );

	if ( isset( $payjpforkintone_setting_data['live-enabled'] ) && 'enable' === $payjpforkintone_setting_data['live-enabled'] ) {
		// Live.
		$secret_key = get_option( 'ht_pay_jp_for_kintone_live_secret_key' );
	} else {
		$secret_key = get_option( 'ht_pay_jp_for_kintone_test_secret_key' );
	}

	return $secret_key;
}

/**
 * フォームに設定された表示言語を取得する.
 *
 * PAY.JP の checkout ダイアログの表示言語設定 ( payjpforkintone-language ) を
 * PAY.JP API の Locale ヘッダーにも流用する。API は Locale: ja を受け取ると
 * エラーメッセージを日本語で返す ( Accept-Language は無視される ).
 *
 * @param int $contact_form_id Contact Form 7 の投稿ID.
 *
 * @return string ja または en.
 */
function ht_payjp_for_kintone_get_locale( $contact_form_id = 0 ) {

	if ( empty( $contact_form_id ) ) {
		$contact_form = WPCF7_ContactForm::get_current();
		if ( ! $contact_form ) {
			return 'ja';
		}
		$contact_form_id = $contact_form->id();
	}

	$payjpforkintone_setting_data = ht_payjp_for_kintone_get_setting_data( $contact_form_id );

	$locale = 'ja';
	if ( isset( $payjpforkintone_setting_data['payjpforkintone-language'] ) && '' !== $payjpforkintone_setting_data['payjpforkintone-language'] ) {
		$locale = $payjpforkintone_setting_data['payjpforkintone-language'];
	}

	return 'en' === $locale ? 'en' : 'ja';
}
