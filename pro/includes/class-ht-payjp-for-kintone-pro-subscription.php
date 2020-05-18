<?php
/**
 * Ht_Payjp_For_Kintone_Payment
 *
 * @package Payjp_For_Kintone
 */

/**
 * Ht_Payjp_For_Kintone_Payment
 */
class HT_Payjp_For_Kintone_Pro_Subscription {

	private $payjp_charged_id;
	private $payjp_charged_captured_at;
	private $payjp_customer_id;
	private $payjp_subscription_id;
	private $payjp_subscription_plan_amount;
	private $payjp_subscription_plan_id;


	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wpcf7_before_send_mail', array( $this, 'subscription' ), 10, 3 );
		add_filter( 'kintone_form_cf7_posted_data_before_post_to_kintone', array( $this, 'set_payjp_subscription_info' ) );

		add_filter( 'form_data_to_kintone_get_update_key', array( $this, 'set_update_key_for_kintone' ), 10, 2 );
//		add_filter( 'form_data_to_kintone_before_wp_remoto_post', array( $this, 'update_kintone_by_payjp_charge_id' ), 10, 2 );

		add_filter( 'form_data_to_kintone_saved', array( $this, 'update_kintone_by_payjp_charge_id' ), 10, 2 );
	}

	public function set_payjp_subscription_info( $cf7_send_data ) {

		$contact_form                 = WPCF7_ContactForm::get_current();
		$payjpforkintone_setting_data = get_post_meta( $contact_form->id(), '_ht_payjpforkintone_setting_data', true );

		if ( 'enable' !== $payjpforkintone_setting_data['payjpforkintone-enabled'] ) {
			return $cf7_send_data;
		}
		if ( isset( $payjpforkintone_setting_data['payment-type'] ) && 'subscription' !== $payjpforkintone_setting_data['payment-type'] ) {
			return $cf7_send_data;
		}

		$cf7_send_data['payjp-charged-id']               = $this->payjp_charged_id;
		$cf7_send_data['payjp-charged-captured-at']      = $this->payjp_charged_captured_at;
		$cf7_send_data['payjp-customer-id']              = $this->payjp_customer_id;
		$cf7_send_data['payjp-subscription-id']          = $this->payjp_subscription_id;
		$cf7_send_data['payjp-subscription-plan-amount'] = $this->payjp_subscription_plan_amount;
		$cf7_send_data['payjp-subscription-plan-id']     = $this->payjp_subscription_plan_id;

		return $cf7_send_data;
	}

	/**
	 * PAY.JP へサブスクリプション決済する.
	 *
	 * @param WPCF7_ContactForm $contact_form .
	 * @param bool              $abort .
	 * @param WPCF7_Submission  $submission .
	 *
	 * @return array
	 */
	public function subscription( $contact_form, &$abort, $submission ) {

		$payjpforkintone_setting_data = get_post_meta(
			$contact_form->id(),
			'_ht_payjpforkintone_setting_data',
			true
		);

		if ( 'enable' !== $payjpforkintone_setting_data['payjpforkintone-enabled'] ) {
			return;
		}
		if ( isset( $payjpforkintone_setting_data['payment-type'] ) && 'subscription' !== $payjpforkintone_setting_data['payment-type'] ) {
			return;
		}

		$posted_data = $submission->get_posted_data();

		if ( isset( $posted_data['payjp-token'] ) && '' !== $posted_data['payjp-token'] ) {

			$token      = sanitize_text_field( wp_unslash( $posted_data['payjp-token'] ) );
			$secret_key = ht_payjp_for_kintone_get_api_key( $contact_form->id() );

			try {
				\Payjp\Payjp::setApiKey( $secret_key );

				$customer     = Payjp\Customer::create(
					array(
						'card' => $token,
					)
				);
				$subscription = Payjp\Subscription::create(
					array(
						'customer' => $customer->id,
						'plan'     => $payjpforkintone_setting_data['payjp-plan-id'],
					)
				);

				$charge = \Payjp\Charge::all(
					array(
						'subscription' => $subscription->id,
					)
				);

				// 日付指定がない場合はすぐにチャージされるのでチャージIDが取得できれば保存
				$posted_data['payjp-charged-id']          = '';
				$posted_data['payjp-charged-captured-at'] = '';
				if ( ! empty( $charge['data'] ) ) {
					$this->payjp_charged_id          = $charge['data'][0]['id'];
					$this->payjp_charged_captured_at = date( 'Y-m-d H:i:s', strtotime( '+9hour', $charge['data'][0]['captured_at'] ) );
				}
				$this->payjp_customer_id              = $customer->id;
				$this->payjp_subscription_id          = $subscription->id;
				$this->payjp_subscription_plan_amount = $subscription->plan->amount;
				$this->payjp_subscription_plan_id     = $subscription->plan->id;

				$mail = $contact_form->prop( 'mail' );

				$mail['body'] = str_replace(
					'[payjp-charged-id]',
					$this->payjp_charged_id,
					$mail['body']
				);
				$mail['body'] = str_replace(
					'[payjp-charged-captured-at]',
					$this->payjp_charged_captured_at,
					$mail['body']
				);
				$mail['body'] = str_replace(
					'[payjp-customer-id]',
					$this->payjp_customer_id,
					$mail['body']
				);
				$mail['body'] = str_replace(
					'[payjp-subscription-id]',
					$this->payjp_subscription_id,
					$mail['body']
				);
				$mail['body'] = str_replace(
					'[payjp-subscription-plan-amount]',
					$this->payjp_subscription_plan_amount,
					$mail['body']
				);
				$mail['body'] = str_replace(
					'[payjp-subscription-plan-id]',
					$this->payjp_subscription_plan_id,
					$mail['body']
				);

				$mail2         = $contact_form->prop( 'mail_2' );
				$mail2['body'] = str_replace(
					'[payjp-charged-id]',
					$this->payjp_charged_id,
					$mail2['body']
				);
				$mail2['body'] = str_replace(
					'[payjp-charged-captured-at]',
					$this->payjp_charged_captured_at,
					$mail2['body']
				);
				$mail2['body'] = str_replace(
					'[payjp-customer-id]',
					$this->payjp_customer_id,
					$mail2['body']
				);
				$mail2['body'] = str_replace(
					'[payjp-subscription-id]',
					$this->payjp_subscription_id,
					$mail2['body']
				);
				$mail2['body'] = str_replace(
					'[payjp-subscription-plan-amount]',
					$this->payjp_subscription_plan_amount,
					$mail2['body']
				);
				$mail2['body'] = str_replace(
					'[payjp-subscription-plan-id]',
					$this->payjp_subscription_plan_id,
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
				ht_payjp_for_kintone_send_error_mail( $e->getMessage() );

			}
		} else {
			// Error.
			$abort = true;
			$submission->set_response( $contact_form->filter_message( __( 'Failed to get credit card information', 'payjp-for-kintone' ) ) );

		}

	}

	public function set_update_key_for_kintone( $update_key, $cf7_send_data ) {

		// PAY.JP の Charge idが存在するなら既にWebhookからkintoneに登録が完了しているため、更新処理をする
		if ( isset( $cf7_send_data['payjp-charged-id'] ) && '' !== $cf7_send_data['payjp-charged-id'] ) {
			$update_key = $cf7_send_data['payjp-charged-id'];
		}

		return $update_key;
	}

	public function update_kintone_by_payjp_charge_id( $res, $body ) {

		// エラーの内容をGETする
		if ( 200 === $res['response']['code'] ) {
			return $res;
		}

		$error_message                          = json_decode( $res['body'], true );
		$contact_form                           = WPCF7_ContactForm::get_current();
		$kintone_field_code_of_payjp_charged_id = HT_Payjp_For_Kintone_Pro_Utility::get_kintone_field_code_of_payjp_information( 'payjp-charged-id', $body['app'], $contact_form );
		$update_flag                            = false;
		foreach ( $error_message['errors'] as $key => $error ) {
			if ( 'record.' . $kintone_field_code_of_payjp_charged_id . '.value' === $key ) {
				foreach ( $error['messages'] as $message ) {
					if ( '値がほかのレコードと重複しています。' === $message ) {
						$update_flag = true;
						break 2;
					}
				}
			}
		}

		if ( $update_flag ) {
			$kintone_setting_data = $contact_form->prop( 'kintone_setting_data' );
			$token                = '';
			foreach ( $kintone_setting_data['app_datas'] as $appdata ) {

				if ( $body['app'] === $appdata['appid'] ) {
					$token = $appdata['token'];
				}
			}

			$kintone_field_code_of_payjp_charged_id = HT_Payjp_For_Kintone_Pro_Utility::get_kintone_field_code_of_payjp_information( 'payjp-charged-id', $body['app'], $contact_form );
			if ( ! empty( $kintone_field_code_of_payjp_charged_id ) ) {

				$kintone_base_data = array(
					'domain'          => $kintone_setting_data['domain'],
					'app'             => $body['app'],
					'token'           => $token,
					'basic_auth_user' => $kintone_setting_data['kintone_basic_authentication_id'],
					'basic_auth_pass' => $kintone_setting_data['kintone_basic_authentication_password'],
				);

				$payjp_charged_id = $body['record'][ $kintone_field_code_of_payjp_charged_id ]['value'];

				$record          = $body['record'];
				$update_key_data = array(
					'field' => $kintone_field_code_of_payjp_charged_id,
					'value' => $payjp_charged_id,
				);
				unset( $record[ $kintone_field_code_of_payjp_charged_id ] );
				$result = Tkc49\Kintone_SDK_For_WordPress\Kintone_API::put( $kintone_base_data, $record, $update_key_data );

				if ( true === $result ) {
					$res['response']['code'] = 200;
				} else {
					$res = $result;
				}
			}
		}

		return $res;
	}


}

new HT_Payjp_For_Kintone_Pro_Subscription();
