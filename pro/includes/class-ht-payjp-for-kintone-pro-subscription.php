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

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wpcf7_posted_data', array( $this, 'subscription' ), 10, 1 );
		add_action( 'wpcf7_before_send_mail', array( $this, 'check_subscription_data' ), 10, 3 );

		add_filter( 'form_data_to_kintone_get_update_key', array( $this, 'set_update_key_for_kintone' ), 10, 2 );
//		add_filter( 'form_data_to_kintone_before_wp_remoto_post', array( $this, 'update_kintone_by_payjp_charge_id' ), 10, 2 );

		add_filter( 'form_data_to_kintone_saved', array( $this, 'update_kintone_by_payjp_charge_id' ), 10, 2 );
	}

	public function check_subscription_data( $contact_form, &$abort, $submission ) {

		$payjpforkintone_setting_data = get_post_meta( $contact_form->id(), '_ht_payjpforkintone_setting_data', true );

		// 有効でない場合は何もせずにリターン.
		if ( 'enable' !== $payjpforkintone_setting_data['payjpforkintone-enabled'] ) {
			return;
		}
		if ( isset( $payjpforkintone_setting_data['payment-type'] ) && 'subscription' !== $payjpforkintone_setting_data['payment-type'] ) {
			return;
		}

		$post_data = $submission->get_posted_data();
		if ( ! isset( $post_data['payjp-customer-id'] ) || empty( $post_data['payjp-customer-id'] ) ) {
			$abort = true;
		}
		if ( ! isset( $post_data['payjp-subscription-id'] ) || empty( $post_data['payjp-subscription-id'] ) ) {
			$abort = true;
		}
		if ( ! isset( $post_data['payjp-subscription-plan-amount'] ) || empty( $post_data['payjp-subscription-plan-amount'] ) ) {
			$abort = true;
		}
		if ( ! isset( $post_data['payjp-subscription-plan-id'] ) || empty( $post_data['payjp-subscription-plan-id'] ) ) {
			$abort = true;
		}

		return;
	}

	/**
	 * PAY.JP へサブスクリプション決済する.
	 *
	 * @param array $posted_data .
	 *
	 * @return array
	 */
	public function subscription( $posted_data ) {

		$contact_form = WPCF7_ContactForm::get_current();
		$submission   = WPCF7_Submission::get_instance();

		$payjpforkintone_setting_data = get_post_meta(
			$contact_form->id(),
			'_ht_payjpforkintone_setting_data',
			true
		);

		if ( 'enable' !== $payjpforkintone_setting_data['payjpforkintone-enabled'] ) {
			return $posted_data;
		}
		if ( isset( $payjpforkintone_setting_data['payment-type'] ) && 'subscription' !== $payjpforkintone_setting_data['payment-type'] ) {
			return $posted_data;
		}

		if ( isset( $_POST['payjp-token'] ) && '' !== $_POST['payjp-token'] ) {

			$token      = sanitize_text_field( wp_unslash( $_POST['payjp-token'] ) );
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
					$posted_data['payjp-charged-id']          = $charge['data'][0]['id'];
					$posted_data['payjp-charged-captured-at'] = date( 'Y-m-d H:i:s', strtotime( '+9hour', $charge['data'][0]['captured_at'] ) );
				}
				$posted_data['payjp-customer-id']              = $customer->id;
				$posted_data['payjp-subscription-id']          = $subscription->id;
				$posted_data['payjp-subscription-plan-amount'] = $subscription->plan->amount;
				$posted_data['payjp-subscription-plan-id']     = $subscription->plan->id;

				return $posted_data;

			} catch ( \Payjp\Error\InvalidRequest $e ) {

				$submission->set_response( $contact_form->filter_message( $e->getMessage() ) );
				ht_payjp_for_kintone_send_error_mail( $contact_form, $e->getMessage() );

				return $posted_data;

			}
		} else {
			// Error.
			$submission->set_response( $contact_form->filter_message( __( 'Failed to get credit card information', 'payjp-for-kintone' ) ) );

			return $posted_data;
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
					'domain' => $kintone_setting_data['domain'],
					'app'    => $body['app'],
					'token'  => $token,
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
