<?php

class HT_Payjp_For_Kintone_Pro_Webhook_Subscription {

	public function __construct() {

		// Webhook Subscription
		add_action( 'wp_ajax_ht_payjp_for_kintone_subscription_by_webhook', array( $this, 'ht_payjp_for_kintone_subscription_by_webhook' ) );
		add_action( 'wp_ajax_nopriv_ht_payjp_for_kintone_subscription_by_webhook', array( $this, 'ht_payjp_for_kintone_subscription_by_webhook' ) );
	}

	private function ht_payjp_for_kintone_post( $kintone_setting_data, $appdata, $data, $kintone_field_code_of_payjp_charged_id, $payjp_charge_succeeded_data_from_webhook, $contact_form ) {
		$kintone = array(
			'domain'          => $kintone_setting_data['domain'],
			'token'           => $appdata['token'],
			'basic_auth_user' => $kintone_setting_data['kintone_basic_authentication_id'],
			'basic_auth_pass' => $kintone_setting_data['kintone_basic_authentication_password'],
			'app'             => $appdata['appid'],
		);

		$result = Tkc49\Kintone_SDK_For_WordPress\Kintone_API::post( $kintone, $data );

		if ( is_wp_error( $result ) ) {
			$error_data = $result->get_error_data();

			$send_error_mail = false;
			if ( isset( $error_data['errors'] ) && ! empty( $error_data['errors'] ) ) {
				foreach ( $error_data['errors'] as $key => $error ) {
					if ( 'record.' . $kintone_field_code_of_payjp_charged_id . '.value' === $key ) {
						foreach ( $error['messages'] as $message ) {
							if ( '値がほかのレコードと重複しています。' !== $message ) {
								$send_error_mail = true;
								break 2;
							}
						}
					}
				}
			} else {
				$send_error_mail = true;
			}

			if ( $send_error_mail ) {

				// エラーメール送信
				$error_message = 'Charged ID: ' . $payjp_charge_succeeded_data_from_webhook['data']['id'] . "\r\n";
				$error_message .= var_export( $error_data, true );

				ht_payjp_for_kintone_send_error_mail( $error_message, $contact_form );
			}
		}
	}

	private function ht_payjp_for_kintone_put( $kintone_setting_data, $appdata, $data, $kintone_field_code_of_payjp_subscription_id, $kintone_field_code_of_payjp_charged_id, $contact_form ) {


		$kintone = array(
			'domain'          => $kintone_setting_data['domain'],
			'token'           => $appdata['token'],
			'basic_auth_user' => $kintone_setting_data['kintone_basic_authentication_id'],
			'basic_auth_pass' => $kintone_setting_data['kintone_basic_authentication_password'],
			'app'             => $appdata['appid'],
		);

		$data[ $kintone_field_code_of_payjp_subscription_id ]['value'] = 'sub_a4848d99f2d12740b3112bb8b7d3';

		// サブスクリプションIDが同じものかつ決済IDがブランクのデータがあるか確認する.
		$query  = $kintone_field_code_of_payjp_subscription_id . ' = "' . $data[ $kintone_field_code_of_payjp_subscription_id ]['value'] . '" and ' . $kintone_field_code_of_payjp_charged_id . ' = ""';
		$fields = array( '$id' );

		$subscription_records = Tkc49\Kintone_SDK_For_WordPress\Kintone_API::getRecords( $kintone, $query, 100, 0, $fields );
		if ( is_wp_error( $subscription_records ) ) {
			// エラー処理
			$error_data = $subscription_records->get_error_data();

			$error_message = 'Webhook：サブスクリプションレコード取得エラー' . "\r\n";
			$error_message .= 'Subscription ID: ' . $data[ $kintone_field_code_of_payjp_subscription_id ]['value'] . "\r\n";
			$error_message .= var_export( $error_data, true );

			ht_payjp_for_kintone_send_error_mail( $error_message, $contact_form );

			return false;
		} else {
			if ( count( $subscription_records ) === 0 ) {
				return false;
			} else {

				// 対象データがあれば更新する
				$kintone = array_merge( $kintone, array( 'id' => $subscription_records[0]['$id']['value'] ) );
				$result  = Tkc49\Kintone_SDK_For_WordPress\Kintone_API::put( $kintone, $data );
				error_log( var_export( $kintone, true ) );
				error_log( var_export( $data, true ) );
				error_log( var_export( $result, true ) );

				if ( is_wp_error( $result ) ) {
					// エラー処理
					$error_data = $result->get_error_data();

					$error_message = 'Webhook：サブスクリプションレコードアップデートエラー' . "\r\n";
					$error_message .= 'Subscription ID: ' . $data[ $kintone_field_code_of_payjp_subscription_id ]['value'] . "\r\n";
					$error_message .= var_export( $error_data, true );

					ht_payjp_for_kintone_send_error_mail( $error_message, $contact_form );

					return false;
				} else {
					// 更新成功
					return true;
				}
			}
		}
	}

	public function ht_payjp_for_kintone_subscription_by_webhook() {

		$payjp_webhook_data_json = file_get_contents( "php://input" );
		$payjp_webhook_data      = json_decode( $payjp_webhook_data_json, true );

		// Pay.JP以外からのWebhookは無効
		$header              = getallheaders();
		$payjp_webhook_token = get_option( 'ht_payjp_for_kintone_source_token_of_webhook' );
		if ( $header['X-Payjp-Webhook-Token'] !== $payjp_webhook_token ) {
			return true;
		}

		// Subscription ID が存在しない場合は定期課金ではないので、処理しない。
		if ( empty( $payjp_webhook_data['data']['subscription'] ) ) {
			return true;
		}

		// Subscriptionでチャージ成功以外は処理しない
		if ( 'charge.succeeded' !== $payjp_webhook_data['type'] ) {
			return true;
		}

		$payjp_charge_succeeded_data_from_webhook = $payjp_webhook_data;

		// @todo 悩みどころ
		$secret_key = get_option( 'ht_pay_jp_for_kintone_live_secret_key' );
//		\Payjp\Payjp::setApiKey( $secret_key );
		\Payjp\Payjp::setApiKey( 'sk_test_5e8079f02a01a66fc8f742f3' );
		$subscription_data = \Payjp\Subscription::retrieve( $payjp_charge_succeeded_data_from_webhook['data']['subscription'] );

		// HT PAY.JP For kintoneで設定したプランかどうか確認する
		$subscription_plan_id                         = $subscription_data->plan['id'];
		$ht_payjp_for_kintone_pro_target_contact_form = new HT_Payjp_For_Kintone_Pro_Target_Contact_Form( $subscription_plan_id );
		if ( ! $ht_payjp_for_kintone_pro_target_contact_form->check_my_plan_subscription_for_ht_payjp_for_kintone() ) {
			return true;
		}

		$contact_form         = $ht_payjp_for_kintone_pro_target_contact_form->get_target_contact_form();
		$kintone_setting_data = $contact_form->prop( 'kintone_setting_data' );

		$target_appdata_for_post = array();
		foreach ( $kintone_setting_data['app_datas'] as $appdata ) {

			$kintone_data_for_post = HT_Payjp_For_Kintone_Pro_Utility::get_data_for_post( $appdata );
			if ( isset( $kintone_data_for_post['setting'] ) ) {
				if ( false === array_search( 'payjp-charged-id', $kintone_data_for_post['setting'], true ) ) {
					// payjp-charged-idの設定がないので、スキップ
					continue;
				}

				// 登録対象
				$target_appdata_for_post[] = $appdata;
			}
		}

		// kintoneへ登録処理
		$data = array();

		foreach ( $target_appdata_for_post as $appdata ) {
			$kintone_field_code_of_payjp_charged_id = HT_Payjp_For_Kintone_Pro_Utility::get_kintone_field_code_of_payjp_information( 'payjp-charged-id', $appdata['appid'], $contact_form );
			if ( $kintone_field_code_of_payjp_charged_id ) {
				$data[ $kintone_field_code_of_payjp_charged_id ] = array(
					'value' => $payjp_charge_succeeded_data_from_webhook['data']['id'],
				);
			}
			$kintone_field_code_of_payjp_captured_at = HT_Payjp_For_Kintone_Pro_Utility::get_kintone_field_code_of_payjp_information( 'payjp-charged-captured-at', $appdata['appid'], $contact_form );
			if ( $kintone_field_code_of_payjp_captured_at ) {
				$data[ $kintone_field_code_of_payjp_captured_at ] = array(
					'value' => date_i18n( 'c', strtotime( '+9hour', $payjp_charge_succeeded_data_from_webhook['data']['captured_at'] ) ),
				);
			}

			$kintone_field_code_of_payjp_customer_id = HT_Payjp_For_Kintone_Pro_Utility::get_kintone_field_code_of_payjp_information( 'payjp-customer-id', $appdata['appid'], $contact_form );
			if ( $kintone_field_code_of_payjp_customer_id ) {
				$data[ $kintone_field_code_of_payjp_customer_id ] = array(
					'value' => $payjp_charge_succeeded_data_from_webhook['data']['customer'],
				);
			}

			$kintone_field_code_of_payjp_subscription_id = HT_Payjp_For_Kintone_Pro_Utility::get_kintone_field_code_of_payjp_information( 'payjp-subscription-id', $appdata['appid'], $contact_form );
			if ( $kintone_field_code_of_payjp_subscription_id ) {
				$data[ $kintone_field_code_of_payjp_subscription_id ] = array(
					'value' => $payjp_charge_succeeded_data_from_webhook['data']['subscription'],
				);
			}

			$kintone_field_code_of_payjp_subscription_plan_amount = HT_Payjp_For_Kintone_Pro_Utility::get_kintone_field_code_of_payjp_information( 'payjp-subscription-plan-amount', $appdata['appid'], $contact_form );
			if ( $kintone_field_code_of_payjp_subscription_plan_amount ) {
				$data[ $kintone_field_code_of_payjp_subscription_plan_amount ] = array(
					'value' => $payjp_charge_succeeded_data_from_webhook['data']['amount'],
				);
			}

			$kintone_field_code_of_payjp_subscription_plan_id = HT_Payjp_For_Kintone_Pro_Utility::get_kintone_field_code_of_payjp_information( 'payjp-subscription-plan-id', $appdata['appid'], $contact_form );
			if ( $kintone_field_code_of_payjp_subscription_plan_id ) {
				$data[ $kintone_field_code_of_payjp_subscription_plan_id ] = array(
					'value' => $subscription_plan_id,
				);
			}
			sleep( 3 );

			// 日付指定の場合は即潔斎されないので、先にCharged IDが空のレコードを調べてUPDATEする
			$result = $this->ht_payjp_for_kintone_put( $kintone_setting_data, $appdata, $data, $kintone_field_code_of_payjp_subscription_id, $kintone_field_code_of_payjp_charged_id, $contact_form );

			if ( ! $result ) {
				// 更新処理をしていないなら新規登録する
				$this->ht_payjp_for_kintone_post( $kintone_setting_data, $appdata, $data, $kintone_field_code_of_payjp_charged_id, $payjp_charge_succeeded_data_from_webhook, $contact_form );
			}
		}

	}
}

new HT_Payjp_For_Kintone_Pro_Webhook_Subscription();

