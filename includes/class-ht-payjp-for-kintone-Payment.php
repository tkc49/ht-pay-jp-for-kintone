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
	 * 支払い処理確定時のUTCタイムスタンプ
	 *
	 * @var integer
	 */
	private $payjp_captured_at;

	/**
	 * 決済金額
	 *
	 * @var number
	 */
	private $amount;

	/**
	 * Customer ID of Pay.jp
	 *
	 * @var string
	 */
	private $payjp_customer_id = '';

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

		$cf7_send_data['payjp-charged-id']          = $this->payjp_charged_id;
		$cf7_send_data['payjp-charged-captured-at'] = $this->payjp_captured_at;
		$cf7_send_data['payjp-customer-id']         = $this->payjp_customer_id;

		$payjpforkintone_setting_data         = get_post_meta( $contact_form->id(), '_ht_payjpforkintone_setting_data', true );
		$amount_cf7_mailtag                   = $payjpforkintone_setting_data['amount-cf7-mailtag'];
		$cf7_send_data[ $amount_cf7_mailtag ] = $this->amount;

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

			// 金額の設定.
			$amount_cf7_mailtag = $payjpforkintone_setting_data['amount-cf7-mailtag'];
			$amount             = $posted_data[ $amount_cf7_mailtag ];

			// descriptionの設定.
			$description = '';
			if ( isset( $payjpforkintone_setting_data['description-cf7-mailtag'] ) && $payjpforkintone_setting_data['description-cf7-mailtag'] !== '' ) {
				$description_cf7_mailtag = $payjpforkintone_setting_data['description-cf7-mailtag'];
				$description             = $posted_data[ $description_cf7_mailtag ];
			}

			if ( is_array( $amount ) ) {
				$amount = $amount[0];
			}

			$amount       = str_replace( ',', '', $amount );
			$amount       = str_replace( '円', '', $amount );
			$amount       = str_replace( '￥', '', $amount );
			$amount       = str_replace( '$', '', $amount );
			$this->amount = $amount;

			/**
			 * PAY.JP 決済直前のアクションフック.
			 *
			 * 決済前にトークンや posted_data を検証し、$abort_payment を true にすると決済を中止できる.
			 *
			 * @param array            $posted_data    CF7 の posted data.
			 * @param int              $amount         請求金額.
			 * @param string           $token          PAY.JP トークン.
			 * @param bool             $abort_payment  中止フラグ（参照渡し）.
			 * @param string           $abort_reason   中止理由（参照渡し、ユーザー表示文言）.
			 * @param WPCF7_Submission $submission     CF7 submission オブジェクト.
			 * @param WPCF7_ContactForm $contact_form  CF7 contact form オブジェクト.
			 */
			$abort_payment = false;
			$abort_reason  = '';
			do_action_ref_array(
				'ht_payjp_for_kintone_before_charge',
				array( $posted_data, $amount, $token, &$abort_payment, &$abort_reason, $submission, $contact_form )
			);
			if ( $abort_payment ) {
				$abort = true;
				if ( '' !== $abort_reason ) {
					$submission->set_response( $contact_form->filter_message( $abort_reason ) );
				}
				return;
			}

			// 都度決済.
			try {
				\Payjp\Payjp::setApiKey( $secret_key );

				// customer 作成が有効な場合.
				$create_customer = isset( $payjpforkintone_setting_data['create-customer'] )
					&& 'enable' === $payjpforkintone_setting_data['create-customer'];

				if ( $create_customer ) {
					// customer を作成.
					$customer                = \Payjp\Customer::create(
						array(
							'card' => $token,
						)
					);
					$this->payjp_customer_id = $customer->id;

					/**
					 * PAY.JP Customer 作成後のアクションフック.
					 *
					 * @param \Payjp\Customer $customer PAY.JP の Customer オブジェクト.
					 */
					do_action( 'ht_payjp_for_kintone_after_customer_create', $customer );

					// customer を使って決済.
					$charge = \Payjp\Charge::create(
						array(
							'customer'    => $customer->id,
							'amount'      => $amount,
							'currency'    => 'jpy',
							'description' => $description,
						)
					);
				} else {
					// 従来どおりトークンで直接決済.
					$charge = \Payjp\Charge::create(
						array(
							'card'        => $token,
							'amount'      => $amount,
							'currency'    => 'jpy',
							'description' => $description,
						)
					);
				}

				$this->payjp_charged_id = $charge->id;
				// captured_at はUTCなので+9時間をする.
				$this->payjp_captured_at = date_i18n( 'Y-m-d H:i:s', $charge->captured_at + ( 9 * 60 * 60 ) );

				/**
				 * PAY.JP 決済完了後のアクションフック.
				 *
				 * @param \Payjp\Charge $charge PAY.JP の Charge オブジェクト.
				 */
				do_action( 'ht_payjp_for_kintone_after_charge', $charge );

				$mail = $contact_form->prop( 'mail' );

				$mail['body'] = str_replace(
					'[payjp-charged-id]',
					$charge->id,
					$mail['body']
				);

				$mail['body'] = str_replace(
					'[payjp-charged-captured-at]',
					$this->payjp_captured_at,
					$mail['body']
				);

				$mail['body'] = str_replace(
					'[payjp-customer-id]',
					$this->payjp_customer_id,
					$mail['body']
				);

				$mail2         = $contact_form->prop( 'mail_2' );
				$mail2['body'] = str_replace(
					'[payjp-charged-id]',
					$charge->id,
					$mail2['body']
				);
				$mail2['body'] = str_replace(
					'[payjp-charged-captured-at]',
					$this->payjp_captured_at,
					$mail2['body']
				);

				$mail2['body'] = str_replace(
					'[payjp-customer-id]',
					$this->payjp_customer_id,
					$mail2['body']
				);

				$contact_form->set_properties(
					array(
						'mail'   => $mail,
						'mail_2' => $mail2,
					)
				);
			} catch ( \Payjp\Error\Card $e ) {
				// カード決済エラーの場合
				$abort = true;
				$submission->set_response( $contact_form->filter_message( __( 'Card payment failed. Please check your card information.', 'payjp-for-kintone' ) ) );
				ht_payjp_for_kintone_send_error_mail( $contact_form, $e->getMessage() );
			} catch ( \Payjp\Error\InvalidRequest $e ) {
				// その他のPAY.JPエラーの場合
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
