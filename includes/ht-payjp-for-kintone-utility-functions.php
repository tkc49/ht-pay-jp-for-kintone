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
