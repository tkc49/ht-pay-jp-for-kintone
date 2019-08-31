<?php
/**
 * Plugin Name:     PAY.JP for kintone
 * Plugin URI:
 * Description:     This Plugin accept payments on your WordPress site via PAY.JP.
 * Author:          Takashi Hosoya
 * Author URI:      https://ht79.info
 * Text Domain:     payjp-for-kintone
 * Domain Path:     /languages
 * Version:         1.0.0
 *
 * @package         Payjp_For_Kintone
 */


define( 'PAY_JP_FOR_KINTONE_URL', plugins_url( '', __FILE__ ) );
define( 'PAY_JP_FOR_KINTONE_PATH', dirname( __FILE__ ) );
$data = get_file_data(
	__FILE__,
	array(
		'ver'   => 'Version',
		'langs' => 'Domain Path',
	)
);
define( 'PAY_JP_FOR_KINTONE_VERSION', $data['ver'] );
define( 'PAY_JP_FOR_KINTONE_LANGS', $data['langs'] );
load_plugin_textdomain(
	'payjp-for-kintone',
	false,
	dirname( plugin_basename( __FILE__ ) ) . PAY_JP_FOR_KINTONE_LANGS
);

require_once 'vendor/autoload.php';
require_once dirname( __FILE__ ) . '/includes/class-payjp-for-kintone.php';
$ht_pay_jp = new Payjp_For_Kintone();
$ht_pay_jp->register();
