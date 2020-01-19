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
	 * 決済完了後にPAY.JP からリターンされるサブスクリプションの金額.
	 *
	 * @var string .
	 */
	private $payjp_subscription_plan_id;


	/**
	 * 決済完了後にPAY.JP からリターンされるサブスクリプションの金額.
	 *
	 * @var string .
	 */
	private $payjp_subscription_amount;

	/**
	 * 決済完了後にPAY.JP からリターンされる顧客のID.
	 *
	 * @var string .
	 */
	private $payjp_customer_id;

	/**
	 * 決済完了後にPAY.JP からリターンされるサブスクリプションのID.
	 *
	 * @var string .
	 */
	private $payjp_subscription_id;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'ht_payjp_for_kintone_do_subscription', array( $this, 'subscription' ), 10, 3 );
		add_filter( 'form_data_to_kintone_post_datas', array( $this, 'add_payjp_subscription_info_to_kintone' ), 10, 3 );
	}

	/**
	 * PAY.JP へサブスクリプション決済する.
	 *
	 * @param string            $token .
	 * @param WPCF7_ContactForm $contact_form .
	 *
	 */
	public function subscription( $token, $contact_form, $submission ) {

		$payjpforkintone_setting_data = get_post_meta(
			$contact_form->id(),
			'_ht_payjpforkintone_setting_data',
			true
		);

		try {
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

			// 顧客IDを保存する.
			$this->payjp_customer_id          = $customer->id;
			$this->payjp_subscription_id      = $subscription->id;
			$this->payjp_subscription_amount  = $subscription->plan->amount;
			$this->payjp_subscription_plan_id = $subscription->plan->id;

			$submited['posted_data'] = $submission->get_posted_data();;
			$submited['posted_data']['payjp-customer-id']     = $customer->id;
			$submited['posted_data']['payjp-subscription-id'] = $subscription->id;

			$mail = $contact_form->prop( 'mail' );

			$mail['body'] = str_replace(
				'[payjp-customer-id]',
				$submited['posted_data']['payjp-customer-id'],
				$mail['body']
			);

			$mail['body'] = str_replace(
				'[payjp-subscription-id]',
				$submited['posted_data']['payjp-subscription-id'],
				$mail['body']
			);

			$mail2         = $contact_form->prop( 'mail_2' );
			$mail2['body'] = str_replace(
				'[payjp-customer-id]',
				$submited['posted_data']['payjp-customer-id'],
				$mail2['body']
			);
			$mail2['body'] = str_replace(
				'[payjp-subscription-id]',
				$submited['posted_data']['payjp-subscription-id'],
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
			ht_payjp_for_kintone_send_error_mail( $contact_form, $e );
		}
	}


	/**
	 * PAY.JPからリターンされるサブスクリプションの情報をkintoneへ保存する.
	 *
	 * @param array  $datas kintoneへ登録するデータ.
	 * @param int    $appid kintoneへ登録するアプリ番号.
	 * @param string $unique_key ユニークキー（アップデータするときに使う）.
	 *
	 * @return array
	 */
	public function add_payjp_subscription_info_to_kintone( $datas, $appid, $unique_key ) {

		$contact_form                 = WPCF7_ContactForm::get_current();
		$payjpforkintone_setting_data = get_post_meta( $contact_form->id(), '_ht_payjpforkintone_setting_data', true );

		// 有効ではない場合は、何もせずにリターン.
		if ( ! isset( $payjpforkintone_setting_data['kintone-enabled'] ) ) {
			return $datas;
		}
		if ( 'enable' !== $payjpforkintone_setting_data['kintone-enabled'] ) {
			return $datas;
		}

		if ( ! isset( $payjpforkintone_setting_data['subscription-enabled'] ) ) {
			return $datas;
		}
		if ( 'enable' !== $payjpforkintone_setting_data['subscription-enabled'] ) {
			return $datas;
		}

		$add_data = array();

		$kintone_fieldcode_for_payjp_subscription_plan_id = $payjpforkintone_setting_data['kintone-fieldcode-for-payjp-subscription-plan-id'];
		if ( $kintone_fieldcode_for_payjp_subscription_plan_id ) {
			$add_data[ $kintone_fieldcode_for_payjp_subscription_plan_id ] = array( 'value' => $this->payjp_subscription_plan_id );
		}

		$kintone_fieldcode_for_payjp_subscription_amount = $payjpforkintone_setting_data['kintone-fieldcode-for-payjp-subscription-amount'];
		if ( $kintone_fieldcode_for_payjp_subscription_amount ) {
			$add_data[ $kintone_fieldcode_for_payjp_subscription_amount ] = array( 'value' => $this->payjp_subscription_amount );
		}

		$kintone_fieldcode_for_payjp_costomer_id = $payjpforkintone_setting_data['kintone-fieldcode-for-payjp-customer-id'];
		if ( $kintone_fieldcode_for_payjp_costomer_id ) {
			$add_data[ $kintone_fieldcode_for_payjp_costomer_id ] = array( 'value' => $this->payjp_customer_id );
		}

		$kintone_fieldcode_for_payjp_subscription_id = $payjpforkintone_setting_data['kintone-fieldcode-for-payjp-subscription-id'];
		if ( $kintone_fieldcode_for_payjp_subscription_id ) {
			$add_data[ $kintone_fieldcode_for_payjp_subscription_id ] = array( 'value' => $this->payjp_subscription_id );
		}

		$datas = array_merge( $datas, $add_data );

		return $datas;

	}

}
