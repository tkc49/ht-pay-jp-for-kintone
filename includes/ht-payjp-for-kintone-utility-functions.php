<?php
function ht_payjp_for_kintone_get_path( $filename = '' ) {
	return HT_PAY_JP_FOR_KINTONE_PATH . ltrim( $filename, '/' );
}

function ht_payjp_for_kintone_include( $filename = '' ) {
	$file_path = ht_payjp_for_kintone_get_path( $filename );
	if ( file_exists( $file_path ) ) {
		include_once( $file_path );
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

	$subject = esc_html__( 'Error : PAY.JP Payment', 'payjp-for-kintone' );
	$body    = $erro_message;
	wp_mail( $to, $subject, $body );
}

function ht_payjp_for_kintone_get_api_key( $contact_form_id ) {

	if ( empty( $contact_form_id ) ) {
		$contact_form    = WPCF7_ContactForm::get_current();
		$contact_form_id = $contact_form->id();
	}

	$payjpforkintone_setting_data = get_post_meta(
		$contact_form_id,
		'_ht_payjpforkintone_setting_data',
		true
	);

	if ( isset( $payjpforkintone_setting_data['live-enabled'] ) && 'enable' === $payjpforkintone_setting_data['live-enabled'] ) {
		// Live.
		$secret_key = get_option( 'ht_pay_jp_for_kintone_live_secret_key' );
	} else {
		$secret_key = get_option( 'ht_pay_jp_for_kintone_test_secret_key' );
	}

	return $secret_key;
}
