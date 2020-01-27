<?php
/**
 * Ht_Payjp_For_Kintone_Payment
 *
 * @package Payjp_For_Kintone
 */

/**
 * Ht_Payjp_For_Kintone_Payment
 */
class HT_Payjp_For_Kintone_Payment {


	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wpcf7_posted_data', array( $this, 'payment_to_pay_jp' ), 10, 1 );
		add_action( 'wpcf7_before_send_mail', array( $this, 'check_charge_id' ), 10, 3 );
	}

	public function check_charge_id( $contact_form, &$abort, $submission ) {

		// 有効でない場合は何もせずにリターン.
		$payjpforkintone_setting_data = get_post_meta( $contact_form->id(), '_ht_payjpforkintone_setting_data', true );
		if ( 'enable' !== $payjpforkintone_setting_data['payjpforkintone-enabled'] ) {
			return;
		}
		if ( isset( $payjpforkintone_setting_data['payment-type'] ) && 'checkout' !== $payjpforkintone_setting_data['payment-type'] ) {
			return;
		}

		$post_data = $submission->get_posted_data();
		if ( ! isset( $post_data['payjp-charged-id'] ) || empty( $post_data['payjp-charged-id'] ) ) {
			$abort = true;
		}

		return;
	}

	/**
	 * PAY.JP へ決済する.
	 *
	 * @param array $posted_data .
	 *
	 * @return array
	 */
	public function payment_to_pay_jp( $posted_data ) {

		$contact_form = WPCF7_ContactForm::get_current();
		$submission   = WPCF7_Submission::get_instance();

		// 有効でない場合は何もせずにリターン.
		$payjpforkintone_setting_data = get_post_meta( $contact_form->id(), '_ht_payjpforkintone_setting_data', true );
		if ( 'enable' !== $payjpforkintone_setting_data['payjpforkintone-enabled'] ) {
			return $posted_data;
		}
		if ( isset( $payjpforkintone_setting_data['payment-type'] ) && 'checkout' !== $payjpforkintone_setting_data['payment-type'] ) {
			return $posted_data;
		}

		if ( isset( $_POST['payjp-token'] ) && '' !== $_POST['payjp-token'] ) {

			$token = sanitize_text_field( wp_unslash( $_POST['payjp-token'] ) );

			$secret_key = ht_payjp_for_kintone_get_api_key( $contact_form->id() );

			$amount_cf7_mailtag = $payjpforkintone_setting_data['amount-cf7-mailtag'];
			$amount             = $posted_data[ $amount_cf7_mailtag ];

			// 都度決済
			try {

				\Payjp\Payjp::setApiKey( $secret_key );

				$charge = \Payjp\Charge::create(
					[
						'card'     => $token,
						'amount'   => $amount,
						'currency' => 'jpy',
					]
				);


				$posted_data['payjp-charged-id'] = $charge->id;

				return $posted_data;

			} catch ( \Payjp\Error\InvalidRequest $e ) {

				$submission->set_response( $contact_form->filter_message( $e->getMessage() ) );
				ht_payjp_for_kintone_send_error_mail( $e->getMessage() );

				return $posted_data;

			}
		} else {
			// Error.
			$submission->set_response( $contact_form->filter_message( __( 'Failed to get credit card information', 'payjp-for-kintone' ) ) );

			return $posted_data;
		}

	}
}
