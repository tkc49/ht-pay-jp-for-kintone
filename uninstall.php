<?php
if ( ! defined( 'ABSPATH' ) && ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

// Delete PAY.JP KEY.
delete_option( 'pay_jp_for_kintone_test_public_key' );
delete_option( 'pay_jp_for_kintone_test_secret_key' );
delete_option( 'pay_jp_for_kintone_live_public_key' );
delete_option( 'pay_jp_for_kintone_live_secret_key' );

// Delete PAY.JP tab information.
delete_post_meta_by_key( '_payjpforkintone_setting_data' );
