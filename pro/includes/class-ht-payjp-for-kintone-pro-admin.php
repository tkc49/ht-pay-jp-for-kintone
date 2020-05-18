<?php
/**
 * HT PAY.JP For kintone Admin の Classファイル
 *
 * @package Payjp_For_Kintone
 * @version 1.0.0
 */

/**
 * Ht_Payjp_For_Kintone_Admin.
 */
class HT_Payjp_For_Kintone_Pro_Admin {

	/**
	 * Admin constructor.
	 */
	public function __construct() {

		require_once HT_PAY_JP_FOR_KINTONE_PATH . 'pro/includes/webhook/class-ht-payjp-for-kintone-pro-webhook-subscription.php';

		// actions
		add_action( 'ht_payjp_for_kintone_after_admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
//		add_action( 'add_explain_ht_payjp_for_kintone_shortcode', array( $this, 'add_explaion_pro_shortcode' ) );

		// fillter
		add_filter( 'ht_payjp_for_kintone_admin_subscription_enabled', array( $this, 'set_subscription_enabled' ), 10, 2 );
		add_filter( 'ht_payjp_for_kintone_admin_payjp_plan_id', array( $this, 'set_payjp_plan_id' ), 10, 2 );

		add_filter( 'ht_payjp_for_kintone_admin_payjp_fixed_subscription_month', array( $this, 'set_payjp_fixed_subscription_month' ), 10, 2 );
		add_filter( 'ht_payjp_for_kintone_admin_payjp_fixed_subscription_time', array( $this, 'set_payjp_fixed_subscription_time' ), 10, 2 );

		add_filter( 'ht_payjp_for_kintone_admin_kintone_fieldcode_for_payjp_subscription_plan_id', array( $this, 'set_kintone_fieldcode_for_payjp_subscription_plan_id' ), 10, 2 );
		add_filter( 'ht_payjp_for_kintone_admin_kintone_fieldcode_for_payjp_subscription_amount', array( $this, 'set_kintone_fieldcode_for_payjp_subscription_amount' ), 10, 2 );
		add_filter( 'ht_payjp_for_kintone_admin_kintone_fieldcode_for_payjp_customer_id', array( $this, 'set_kintone_fieldcode_for_payjp_customer_id' ), 10, 2 );
		add_filter( 'ht_payjp_for_kintone_admin_kintone_fieldcode_for_payjp_subscription_id', array( $this, 'set_kintone_fieldcode_for_payjp_subscription_id' ), 10, 2 );

		add_action( 'ht_payjp_for_kintone_after_setting_page', array( $this, 'set_ht_payjp_for_kintone_licence_block' ) );
		add_action( 'ht_payjp_for_kintone_admin_setting_update', array( $this, 'update_licence_key' ) );

		add_filter( 'wpcf7_collect_mail_tags', array( $this, 'set_payjp_subscription_info_of_kintone_form' ), 10, 3 );

	}

	public function set_payjp_subscription_info_of_kintone_form( $mailtags, $args, $contac_form ) {

		$mailtags[] = 'payjp-charged-captured-at';
		$mailtags[] = 'payjp-customer-id';
		$mailtags[] = 'payjp-subscription-id';
		$mailtags[] = 'payjp-subscription-plan-amount';
		$mailtags[] = 'payjp-subscription-plan-id';


		return $mailtags;

	}


	/**
	 *  admin_enqueue_scripts
	 */
	public function admin_enqueue_scripts() {
		wp_enqueue_script( 'ht-payjp-for-kintone-pro' );
	}

	public function set_subscription_enabled( $subscription_enabled, $post ) {

		$payjpforkintone_setting_data = get_post_meta( $post->id(), '_ht_payjpforkintone_setting_data', true );

		$payjpforkintone_setting_data = wp_parse_args(
			$payjpforkintone_setting_data,
			array(
				'payjpforkintone-enabled' => 'disable',
				'live-enabled'            => false,
			)
		);

		if ( isset( $payjpforkintone_setting_data['payment-type'] ) ) {
			$subscription_enabled = $payjpforkintone_setting_data['payment-type'];
		}

		return $subscription_enabled;
	}

	public function set_payjp_plan_id( $payjp_plan_id, $post ) {

		$payjpforkintone_setting_data = get_post_meta( $post->id(), '_ht_payjpforkintone_setting_data', true );

		$payjpforkintone_setting_data = wp_parse_args(
			$payjpforkintone_setting_data,
			array(
				'payjpforkintone-enabled' => 'disable',
				'live-enabled'            => false,
			)
		);

		if ( isset( $payjpforkintone_setting_data['payjp-plan-id'] ) ) {
			$payjp_plan_id = $payjpforkintone_setting_data['payjp-plan-id'];
		}

		return $payjp_plan_id;

	}

	public function set_payjp_fixed_subscription_month( $payjp_fixed_subscription_month, $post ) {

		$payjpforkintone_setting_data = get_post_meta( $post->id(), '_ht_payjpforkintone_setting_data', true );

		$payjpforkintone_setting_data = wp_parse_args(
			$payjpforkintone_setting_data,
			array(
				'payjpforkintone-enabled' => 'disable',
				'live-enabled'            => false,
			)
		);

		if ( isset( $payjpforkintone_setting_data['payjp-fixed-subscription-month'] ) ) {
			$payjp_fixed_subscription_month = $payjpforkintone_setting_data['payjp-fixed-subscription-month'];
		}

		return $payjp_fixed_subscription_month;
	}

	public function set_payjp_fixed_subscription_time( $payjp_fixed_subscription_time, $post ) {

		$payjpforkintone_setting_data = get_post_meta( $post->id(), '_ht_payjpforkintone_setting_data', true );

		$payjpforkintone_setting_data = wp_parse_args(
			$payjpforkintone_setting_data,
			array(
				'payjpforkintone-enabled' => 'disable',
				'live-enabled'            => false,
			)
		);

		if ( isset( $payjpforkintone_setting_data['payjp-fixed-subscription-time'] ) ) {
			$payjp_fixed_subscription_time = $payjpforkintone_setting_data['payjp-fixed-subscription-time'];
		}

		return $payjp_fixed_subscription_time;
	}

	public function set_kintone_fieldcode_for_payjp_subscription_plan_id( $kintone_fieldcode_for_payjp_subscription_plan_id, $post ) {

		$payjpforkintone_setting_data = get_post_meta( $post->id(), '_ht_payjpforkintone_setting_data', true );

		$payjpforkintone_setting_data = wp_parse_args(
			$payjpforkintone_setting_data,
			array(
				'payjpforkintone-enabled' => 'disable',
				'live-enabled'            => false,
			)
		);

		if ( isset( $payjpforkintone_setting_data['kintone-fieldcode-for-payjp-subscription-plan-id'] ) ) {
			$kintone_fieldcode_for_payjp_subscription_plan_id = $payjpforkintone_setting_data['kintone-fieldcode-for-payjp-subscription-plan-id'];
		}

		return $kintone_fieldcode_for_payjp_subscription_plan_id;

	}

	public function set_kintone_fieldcode_for_payjp_subscription_amount( $kintone_fieldcode_for_payjp_subscription_amount, $post ) {

		$payjpforkintone_setting_data = get_post_meta( $post->id(), '_ht_payjpforkintone_setting_data', true );

		$payjpforkintone_setting_data = wp_parse_args(
			$payjpforkintone_setting_data,
			array(
				'payjpforkintone-enabled' => 'disable',
				'live-enabled'            => false,
			)
		);

		if ( isset( $payjpforkintone_setting_data['kintone-fieldcode-for-payjp-subscription-amount'] ) ) {
			$kintone_fieldcode_for_payjp_subscription_amount = $payjpforkintone_setting_data['kintone-fieldcode-for-payjp-subscription-amount'];
		}

		return $kintone_fieldcode_for_payjp_subscription_amount;

	}

	public function set_kintone_fieldcode_for_payjp_customer_id( $kintone_fieldcode_for_payjp_customer_id, $post ) {

		$payjpforkintone_setting_data = get_post_meta( $post->id(), '_ht_payjpforkintone_setting_data', true );

		$payjpforkintone_setting_data = wp_parse_args(
			$payjpforkintone_setting_data,
			array(
				'payjpforkintone-enabled' => 'disable',
				'live-enabled'            => false,
			)
		);

		if ( isset( $payjpforkintone_setting_data['kintone-fieldcode-for-payjp-customer-id'] ) ) {
			$kintone_fieldcode_for_payjp_customer_id = $payjpforkintone_setting_data['kintone-fieldcode-for-payjp-customer-id'];
		}

		return $kintone_fieldcode_for_payjp_customer_id;

	}

	public function set_kintone_fieldcode_for_payjp_subscription_id( $kintone_fieldcode_for_payjp_subscription_id, $post ) {

		$payjpforkintone_setting_data = get_post_meta( $post->id(), '_ht_payjpforkintone_setting_data', true );

		$payjpforkintone_setting_data = wp_parse_args(
			$payjpforkintone_setting_data,
			array(
				'payjpforkintone-enabled' => 'disable',
				'live-enabled'            => false,
			)
		);

		if ( isset( $payjpforkintone_setting_data['kintone-fieldcode-for-payjp-subscription-id'] ) ) {
			$kintone_fieldcode_for_payjp_subscription_id = $payjpforkintone_setting_data['kintone-fieldcode-for-payjp-subscription-id'];
		}

		return $kintone_fieldcode_for_payjp_subscription_id;

	}

	public function set_ht_payjp_for_kintone_licence_block() {

		$ht_payjp_for_kintone_licence_key             = get_option( 'ht_payjp_for_kintone_licence_key' );
		$ht_payjp_for_kintone_source_token_of_webhook = get_option( 'ht_payjp_for_kintone_source_token_of_webhook' );
		?>

		<h2>Setting HT PAY.JP for kintone PRO</h2>
		<table class="form-table">
			<tbody>
			<tr valign="top">
				<th scope="row">
					<label for="add_text">
						<?php esc_html_e( 'HT PAY.JP for kintone PRO\'s licence key', 'payjp-for-kintone' ); ?>
					</label>
				</th>
				<td>
					<input type="text" name="ht-payjp-for-kintone-licence-key" class="regular-text" value="<?php echo esc_attr( $ht_payjp_for_kintone_licence_key ); ?>">
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="add_text">
						<?php esc_html_e( 'PAY.JP Source token of Webhook', 'payjp-for-kintone' ); ?>
					</label>
				</th>
				<td>
					<input type="text" name="ht-payjp-for-kintone-source-token-of-webhook" class="regular-text" value="<?php echo esc_attr( $ht_payjp_for_kintone_source_token_of_webhook ); ?>"><br>
					<a href="https://pay.jp/d/settings" target="_blank">https://pay.jp/d/settings</a><br>
					■ Webhook for Subscription<br>
					<?php echo admin_url( 'admin-ajax.php?action=ht_payjp_for_kintone_subscription_by_webhook' ); ?>
				</td>
			</tr>
			</tbody>
		</table>
		<?php
	}

	public function update_licence_key() {
		$safe_licence_key = '';
		if ( isset( $_POST['ht-payjp-for-kintone-licence-key'] ) ) {
			$licence_key      = (string) filter_input( INPUT_POST, 'ht-payjp-for-kintone-licence-key' );
			$safe_licence_key = sanitize_text_field( $licence_key );
		}
		update_option( 'ht_payjp_for_kintone_licence_key', $safe_licence_key );

		$safe_source_token = '';
		if ( isset( $_POST['ht-payjp-for-kintone-source-token-of-webhook'] ) ) {
			$source_token      = (string) filter_input( INPUT_POST, 'ht-payjp-for-kintone-source-token-of-webhook' );
			$safe_source_token = sanitize_text_field( $source_token );
		}
		update_option( 'ht_payjp_for_kintone_source_token_of_webhook', $safe_source_token );

	}

	public function add_explaion_pro_shortcode() {
		esc_html_e( 'Paste the following shortcode of Contact form 7 on form of Contact form 7 when selected subscription', 'payjp-for-kintone' );
		?>

		<span class="shortcode wp-ui-highlight">
			<input type="text" id="payjpforkintone-shortcode-of-payjp-charged-captured-at" onfocus="this.select();" readonly="readonly" class="large-text code" value="[hidden payjp-charged-captured-at]">
		</span>
		<span class="shortcode wp-ui-highlight">
			<input type="text" id="payjpforkintone-shortcode-of-customer-id" onfocus="this.select();" readonly="readonly" class="large-text code" value="[hidden payjp-customer-id]">
		</span>
		<span class="shortcode wp-ui-highlight">
			<input type="text" id="payjpforkintone-shortcode-of-subscription-id" onfocus="this.select();" readonly="readonly" class="large-text code" value="[hidden payjp-subscription-id]">
		</span>
		<span class="shortcode wp-ui-highlight">
			<input type="text" id="payjpforkintone-shortcode-of-subscription-plan-id" onfocus="this.select();" readonly="readonly" class="large-text code" value="[hidden payjp-subscription-plan-id]">
		</span>
		<span class="shortcode wp-ui-highlight">
			<input type="text" id="payjpforkintone-shortcode-of-subscription-plan-amount" onfocus="this.select();" readonly="readonly" class="large-text code" value="[hidden payjp-subscription-plan-amount]">
		</span>

		<?php
	}

}

new HT_Payjp_For_Kintone_Pro_Admin();
