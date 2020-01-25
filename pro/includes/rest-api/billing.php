<?php
add_action(
	'rest_api_init',
	function() {
		register_rest_route(
			'ht-payjp-for-kintone-pro/v2',
			'/get_transfer',
			array(
				'methods'  => 'GET',
				'callback' => 'ht_payjp_for_kintone_pro_get_transfer',
			)
		);
	}
);

function ht_payjp_for_kintone_pro_get_billing_subscription_ids() {

	$subscription_ids = array();

	$subscription_list = \Payjp\Subscription::all( array( 'limit' => 100 ) );

	foreach ( $subscription_list['data'] as $subscription ) {
		// サブスクリプションに含まれているPLANIDを取得
		$plan_id                                      = $subscription->plan->id;
		$ht_payjp_for_kintone_pro_target_contact_form = new HT_Payjp_For_Kintone_Pro_Target_Contact_Form( $plan_id );
		if ( $ht_payjp_for_kintone_pro_target_contact_form->check_my_plan_subscription_for_ht_payjp_for_kintone() ) {
			$subscription_ids[] = $subscription->id;
		}
	}

	return $subscription_ids;
}

function ht_payjp_for_kintone_pro_get_all_charge( $first_date, $last_date, $charge_list = array(), $offset = 0, $has_more = true ) {

	$max_limit = 100;

	if ( $has_more ) {
		$charge_result = \Payjp\Charge::all(
			array(
				'since'  => strtotime( $first_date ),
				'until'  => strtotime( $last_date ),
				'limit'  => $max_limit,
				'offset' => $offset,
			)
		);
		foreach ( $charge_result['data'] as $charge ) {
			$charge_list[] = $charge;
		}

		$offset = $offset + $max_limit;
		ht_payjp_for_kintone_pro_get_all_charge( $first_date, $last_date, $charge_list, $offset, $charge_result->has_more );
	}

	return $charge_list;
}

function ht_payjp_for_kintone_pro_get_transfer( WP_REST_Request $req ) {

	// コードを発行し、そのコードを管理画面から設定をしてもらう。
	// service.ht79.info からそのコードをパラメータにつけて処理を実行する.
	// 同じコードの場合は認証OK
	$licence_key = get_option( 'ht_payjp_for_kintone_licence_key' );
	if ( $req['licence_key'] !== $licence_key ) {
		return array( 'error_message' => 'Licence key does not match' );
	}

	$target_month = $req['target-yyyy-mm'];
	$first_date   = date( 'Y-m-d', strtotime( 'first day of ' . $target_month ) );
	$last_date    = date( 'Y-m-d', strtotime( 'last day of ' . $target_month ) );

	// @todo liveに変更するべき
	$secret_key = get_option( 'ht_pay_jp_for_kintone_live_secret_key' );
//	\Payjp\Payjp::setApiKey( $secret_key );
	\Payjp\Payjp::setApiKey( 'sk_test_5e8079f02a01a66fc8f742f3' );

	// 請求対象のサブスクリプションIDを取得する
	$target_subscription_ids     = ht_payjp_for_kintone_pro_get_billing_subscription_ids();
	$target_charge_list_of_month = ht_payjp_for_kintone_pro_get_all_charge( $first_date, $last_date );

	$target_billing_of_charge_list = array();
	foreach ( $target_charge_list_of_month as $target_charge ) {
		$subscription_id = $target_charge->subscription;
		foreach ( $target_subscription_ids as $target_subscription_id ) {
			if ( $subscription_id === $target_subscription_id ) {
				// 課金対象のCharge(支払い)
				$target_billing_of_charge_list[ $target_charge->id ]['amount']          = $target_charge->amount;
				$target_billing_of_charge_list[ $target_charge->id ]['amount_refunded'] = $target_charge->amount_refunded;
				$target_billing_of_charge_list[ $target_charge->id ]['captured']        = $target_charge->amount_refunded;
				$target_billing_of_charge_list[ $target_charge->id ]['currency']        = $target_charge->currency;
				$target_billing_of_charge_list[ $target_charge->id ]['livemode']        = $target_charge->livemode;
				$target_billing_of_charge_list[ $target_charge->id ]['refunded']        = $target_charge->refunded;
				$target_billing_of_charge_list[ $target_charge->id ]['subscription']    = $target_charge->subscription;
				$target_billing_of_charge_list[ $target_charge->id ]['created']         = $target_charge->created;
			}
		}
	}

	return $target_billing_of_charge_list;

}
