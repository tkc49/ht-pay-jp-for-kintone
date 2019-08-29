<?php
/**
 * Payment
 *
 * @package Pay_Jp_For_Kintone
 */

/**
 * Payment
 */
class Payment {

	private $payjp_biling_id;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wpcf7_before_send_mail', array( $this, 'payment_to_pay_jp' ), 10, 3 );
		add_filter( 'form_data_to_kintone_post_datas', array( $this, 'add_payjp_billing_id_to_kintone' ), 10, 3 );
	}

	/**
	 * PAY.JP へ決済する.
	 *
	 * @param WPCF7_ContactForm $contact_form .
	 * @param boolean           $abort .
	 * @param WPCF7_Submission  $submission .
	 */
	public function payment_to_pay_jp( $contact_form, $abort, $submission ) {

		if ( isset( $_POST['payjp-token'] ) && $_POST['payjp-token'] !== '' ) {

			$token = wp_unslash( $_POST['payjp-token'] );

			$payjpforkintone_setting_data = get_post_meta( $contact_form->id(), '_payjpforkintone_setting_data', true );

			if ( isset( $payjpforkintone_setting_data['live-enabled'] ) && 'enable' === $payjpforkintone_setting_data['live-enabled'] ) {
				// Live.
				$secret_key = get_option( 'pay_jp_for_kintone_live_secret_key' );
			} else {
				$secret_key = get_option( 'pay_jp_for_kintone_test_secret_key' );
			}

			$amount_cf7_mailtag = $payjpforkintone_setting_data['amount-cf7-mailtag'];
			$post_data          = $submission->get_posted_data();
			$amount             = $post_data[ $amount_cf7_mailtag ];

			\Payjp\Payjp::setApiKey( $secret_key );
			try {
				$charge = \Payjp\Charge::create(
					[
						'card'     => $token,
						'amount'   => $amount,
						'currency' => 'jpy',
					]
				);
				error_log( '成功' );
				error_log( var_export( $charge, true ) );

				// IDを保存する.
				$this->payjp_biling_id = $charge->id;

			} catch ( \Payjp\Error\InvalidRequest $e ) {
				error_log( '失敗' );
				error_log( var_export( $e, true ) );
				$abort = true;
			}

		} else {
			// Error.
			$abort = true;
		}

	}

	/**
	 * PAY.JPからリターンされる課金IDをkintoneへ保存する.
	 *
	 * @param array  $datas kintoneへ登録するデータ.
	 * @param int    $appid kintoneへ登録するアプリ番号.
	 * @param string $unique_key ユニークキー（アップデータするときに使う）.
	 *
	 * @return array
	 */
	public function add_payjp_billing_id_to_kintone( $datas, $appid, $unique_key ) {

		$contact_form                 = WPCF7_ContactForm::get_current();
		$payjpforkintone_setting_data = get_post_meta( $contact_form->id(), '_payjpforkintone_setting_data', true );

		// 有効ではない場合は、何もせずにリターン.
		if ( 'enable' !== $payjpforkintone_setting_data['kintone-enabled'] ) {
			return $datas;
		}

		$kintone_fieldcode_for_payjp_billing_id = $payjpforkintone_setting_data['kintone-fieldcode-for-payjp-billing-id'];

		if ( empty( $kintone_fieldcode_for_payjp_billing_id ) ) {
			return $datas;
		}

		$add_data = array();

		$add_data[ $kintone_fieldcode_for_payjp_billing_id ] = array( 'value' => $this->payjp_biling_id );

		$datas = array_merge(
			$datas,
			$add_data
		);

		return $datas;

	}

}
