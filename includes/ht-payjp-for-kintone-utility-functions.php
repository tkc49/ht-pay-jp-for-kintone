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

function ht_payjp_for_kintone_send_error_mail( $contact_form, $e ) {

	$kintone_setting_data = $contact_form->prop( 'kintone_setting_data' );

	if ( empty( $kintone_setting_data ) ) {
		return;
	}

	$error_msg = $e->getMessage();

	$email_address_to_send_kintone_registration_error = $kintone_setting_data['email_address_to_send_kintone_registration_error'];

	if ( $email_address_to_send_kintone_registration_error ) {
		$to = $email_address_to_send_kintone_registration_error;
	} else {
		$to = get_option( 'admin_email' );
	}

	$subject = esc_html__( 'Error : PAY.JP Payment', 'payjp-for-kintone' );
	$body    = $error_msg;
	wp_mail( $to, $subject, $body );
}
