<?php
/**
 * Plugin Name:     HT PAY.JP for kintone
 * Plugin URI:
 * Description:     This Plugin accept payments on your WordPress site via PAY.JP.
 * Author:          Takashi Hosoya
 * Author URI:      https://ht79.info
 * Text Domain:     ht-pay-jp-for-kintone
 * Domain Path:     /languages
 * Version:         1.4.1
 *
 * @package         HT_Payjp_For_Kintone
 */


define( 'HT_PAY_JP_FOR_KINTONE_URL', plugins_url( '', __FILE__ ) );
define( 'HT_PAY_JP_FOR_KINTONE_PATH', plugin_dir_path( __FILE__ ) );
$data = get_file_data(
	__FILE__,
	array(
		'ver'   => 'Version',
		'langs' => 'Domain Path',
	)
);
define( 'HT_PAY_JP_FOR_KINTONE_VERSION', $data['ver'] );
define( 'HT_PAY_JP_FOR_KINTONE_LANGS', $data['langs'] );
load_plugin_textdomain(
	'payjp-for-kintone',
	false,
	dirname( plugin_basename( __FILE__ ) ) . HT_PAY_JP_FOR_KINTONE_LANGS
);

require_once 'vendor/autoload.php';
require_once HT_PAY_JP_FOR_KINTONE_PATH . 'includes/class-ht-Payjp-for-kintone.php';
require_once HT_PAY_JP_FOR_KINTONE_PATH . 'includes/ht-payjp-for-kintone-utility-functions.php';
ht_payjp_for_kintone_include( 'pro/ht-payjp-for-kintone-pro.php' );

$ht_pay_jp = new HT_Payjp_For_Kintone();
$ht_pay_jp->register();


function ht79_payjp_for_kintone_pro_activate_autoupdate() {

	$filename  = 'pro/ht-payjp-for-kintone-pro.php';
	$file_path = ht_payjp_for_kintone_get_path( $filename );
	if ( file_exists( $file_path ) ) {
		$my_update_checker = Puc_v4_Factory::buildUpdateChecker(
			'https://github.com/tkc49/PAY.JP-for-kintone-pro/',
			__FILE__,
			'ht-payjp-for-kintone'
		);

		// Optional: If you're using a private repository, specify the access token like this: .
		$my_update_checker->setAuthentication( GITHUB_ACCESS_TOKEN );
		$my_update_checker->getVcsApi()->enableReleaseAssets();

	}
}

add_action( 'plugins_loaded', 'ht79_payjp_for_kintone_pro_activate_autoupdate' );
