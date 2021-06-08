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
	 * Charged ID of Pay.jp
	 *
	 * @var string
	 */
	private $payjp_charged_id;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wpcf7_before_send_mail', array( $this, 'payment_to_pay_jp' ), 10, 3 );
		add_filter( 'kintone_form_cf7_posted_data_before_post_to_kintone', array( $this, 'set_payjp_charged_id' ) );
	}


	/**
	 * Get Charged ID of Pay.jp
	 *
	 * @param array $cf7_send_data .
	 *
	 * @return array .
	 */
	public function set_payjp_charged_id( $cf7_send_data ) {

		$contact_form                 = WPCF7_ContactForm::get_current();
		$payjpforkintone_setting_data = get_post_meta( $contact_form->id(), '_ht_payjpforkintone_setting_data', true );

		// 有効ではない場合は、何もせずにリターン.
		if ( 'enable' !== $payjpforkintone_setting_data['payjpforkintone-enabled'] ) {
			return $cf7_send_data;
		}
		if ( isset( $payjpforkintone_setting_data['payment-type'] ) && 'checkout' !== $payjpforkintone_setting_data['payment-type'] ) {
			return $cf7_send_data;
		}

		$cf7_send_data['payjp-charged-id'] = $this->payjp_charged_id;

		return $cf7_send_data;
	}

	/**
	 * PAY.JP へ決済する.
	 *
	 * @param WPCF7_ContactForm $contact_form .
	 * @param boolean           $abort .
	 * @param WPCF7_Submission  $submission .
	 */
	public function payment_to_pay_jp( $contact_form, &$abort, $submission ) {

		// 有効でない場合は何もせずにリターン.
		$payjpforkintone_setting_data = get_post_meta( $contact_form->id(), '_ht_payjpforkintone_setting_data', true );
		if ( 'enable' !== $payjpforkintone_setting_data['payjpforkintone-enabled'] ) {
			return;
		}
		if ( isset( $payjpforkintone_setting_data['payment-type'] ) && 'checkout' !== $payjpforkintone_setting_data['payment-type'] ) {
			return;
		}

		$posted_data = $submission->get_posted_data();

		if ( isset( $posted_data['payjp-token'] ) && '' !== $posted_data['payjp-token'] ) {

			$token = sanitize_text_field( wp_unslash( $posted_data['payjp-token'] ) );

			$secret_key = ht_payjp_for_kintone_get_api_key( $contact_form->id() );

			$amount_cf7_mailtag = $payjpforkintone_setting_data['amount-cf7-mailtag'];
			$amount             = $posted_data[ $amount_cf7_mailtag ];

			if ( is_array( $amount ) ) {
				$amount = $amount[0];
			}

			// 都度決済.
			try {

				\Payjp\Payjp::setApiKey( $secret_key );

				$charge = \Payjp\Charge::create(
					array(
						'card'     => $token,
						'amount'   => $amount,
						'currency' => 'jpy',
					)
				);

				$this->payjp_charged_id = $charge->id;

				$mail = $contact_form->prop( 'mail' );

				$mail['body'] = str_replace(
					'[payjp-charged-id]',
					$charge->id,
					$mail['body']
				);

				$mail2         = $contact_form->prop( 'mail_2' );
				$mail2['body'] = str_replace(
					'[payjp-charged-id]',
					$charge->id,
					$mail2['body']
				);

				$contact_form->set_properties(
					array(
						'mail'   => $mail,
						'mail_2' => $mail2,
					)
				);

			} catch ( \Payjp\Error\InvalidRequest $e ) {

				$abort = true;
				$submission->set_response( $contact_form->filter_message( $e->getMessage() ) );
				ht_payjp_for_kintone_send_error_mail( $contact_form, $e->getMessage() );

			}
		} else {
			// Error.
			$abort = true;
			$submission->set_response( $contact_form->filter_message( __( 'Failed to get credit card information', 'payjp-for-kintone' ) ) );
		}

	}
}
