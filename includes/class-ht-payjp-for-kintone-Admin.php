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
class HT_Payjp_For_Kintone_Admin {

	/**
	 * Nonce
	 *
	 * @var string
	 */
	private $nonce = 'ht_payjp-for-kintone-admin-setting-page';

	/**
	 * Admin constructor.
	 */
	public function __construct() {

		add_action( 'admin_enqueue_scripts', array( $this, 'ht_payjpforkintone_admin_enqueue_scripts' ) );
		add_action( 'admin_menu', array( $this, 'ht_payjpforkintone_admin_add_page' ) );

		// Add PAY.JP setting panel to CF7.
		add_filter( 'wpcf7_editor_panels', array( $this, 'ht_payjpforkintone_editor_panels' ), 30 );
		add_filter(
			'wpcf7_contact_form_properties',
			array( $this, 'ht_payjpforkintone_add_properties_to_contact_form_properties' ),
			10,
			2
		);

		// 保存したときに実行.
		add_action( 'wpcf7_save_contact_form', array( $this, 'ht_payjpforkintone_save_contact_form' ), 10, 3 );

		add_filter( 'wpcf7_collect_mail_tags', array( $this, 'set_payjp_charged_id_of_kintone_form' ), 10, 3 );
	}

	public function set_payjp_charged_id_of_kintone_form( $mailtags, $args, $contac_form ) {

		$mailtags[] = 'payjp-charged-id';
		$mailtags[] = 'payjp-charged-captured-at';
		$mailtags[] = 'payjp-customer-id';

		return $mailtags;
	}

	/**
	 * CF7で保存したとき.
	 *
	 * @param WPCF7_ContactForm $contact_form .
	 * @param array             $args .
	 * @param string            $context .
	 */
	public function ht_payjpforkintone_save_contact_form( $contact_form, $args, $context ) {
		$properties                                    = array();
		$properties['ht_payjpforkintone_setting_data'] = $args['ht_payjpforkintone_setting_data'];
		$contact_form->set_properties( $properties );
	}

	/**
	 * CF7のメニューにオプションページのメニューを追加する.
	 */
	public function ht_payjpforkintone_admin_add_page() {
		add_submenu_page(
			'wpcf7',
			'HT PAY.JP for kintone',
			'HT PAY.JP for kintone',
			WPCF7_ADMIN_READ_WRITE_CAPABILITY,
			'htpayjpforkintone',
			array( $this, 'ht_payjpforkintone_options_page_handler' )
		);
	}

	/**
	 * PAY.JP for kintone オプションページ作成.
	 */
	public function ht_payjpforkintone_options_page_handler() {

		if ( ! empty( $_POST ) && check_admin_referer( $this->nonce ) ) {
			$result = $this->update();
			if ( ! empty( $result['errors'] ) ) {
				foreach ( $result['errors'] as $error_message ) {
					printf(
						'<div class="error notice is-dismissible"><p>%s</p></div>',
						esc_html( $error_message )
					);
				}
			}
			if ( $result['success'] ) {
				echo '<div class="updated notice is-dismissible"><p><strong>' . esc_html__( 'Success', 'payjp-for-kintone' ) . '</strong></p></div>';
			}
		}

		$test_secret_key = get_option( 'ht_pay_jp_for_kintone_test_secret_key' );
		$test_public_key = get_option( 'ht_pay_jp_for_kintone_test_public_key' );
		$live_secret_key = get_option( 'ht_pay_jp_for_kintone_live_secret_key' );
		$live_public_key = get_option( 'ht_pay_jp_for_kintone_live_public_key' );

		$test_secret_mask = self::mask_secret_key( $test_secret_key );
		$live_secret_mask = self::mask_secret_key( $live_secret_key );

		$secret_placeholder = __( '変更しない場合は空欄のままにしてください', 'payjp-for-kintone' );

		?>
		<div class="wrap">
			<h2>PAY.JP for kintone Settings</h2>
			<form id="payjp-for-kintone-form" method="post" action="" autocomplete="off">

				<?php wp_nonce_field( $this->nonce ); ?>

				<table class="form-table">
					<tbody>
					<tr valign="top">
						<th scope="row">
							<label for="test-secret-key">
								<?php esc_html_e( 'Test Secret Key', 'payjp-for-kintone' ); ?>
							</label>
						</th>
						<td>
							<input type="password" id="test-secret-key" name="test-secret-key" class="regular-text"
								value="" autocomplete="off" spellcheck="false"
								data-lpignore="true" data-1p-ignore="true" data-bwignore="true"
								placeholder="<?php echo esc_attr( $secret_placeholder ); ?>">
							<?php if ( '' !== $test_secret_mask ) : ?>
								<p class="description">
									<?php esc_html_e( 'Current:', 'payjp-for-kintone' ); ?>
									<code><?php echo esc_html( $test_secret_mask ); ?></code>
								</p>
							<?php endif; ?>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="test-public-key">
								<?php esc_html_e( 'Test Public Key', 'payjp-for-kintone' ); ?>
							</label>
						</th>
						<td>
							<input type="text" id="test-public-key" name="test-public-key" class="regular-text"
								value="<?php echo esc_attr( $test_public_key ); ?>" spellcheck="false">
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="live-secret-key">
								<?php esc_html_e( 'Live Secret Key', 'payjp-for-kintone' ); ?>
							</label>
						</th>
						<td>
							<input type="password" id="live-secret-key" name="live-secret-key" class="regular-text"
								value="" autocomplete="off" spellcheck="false"
								data-lpignore="true" data-1p-ignore="true" data-bwignore="true"
								placeholder="<?php echo esc_attr( $secret_placeholder ); ?>">
							<?php if ( '' !== $live_secret_mask ) : ?>
								<p class="description">
									<?php esc_html_e( 'Current:', 'payjp-for-kintone' ); ?>
									<code><?php echo esc_html( $live_secret_mask ); ?></code>
								</p>
							<?php endif; ?>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="live-public-key">
								<?php esc_html_e( 'Live Public Key', 'payjp-for-kintone' ); ?>
							</label>
						</th>
						<td>
							<input type="text" id="live-public-key" name="live-public-key" class="regular-text"
								value="<?php echo esc_attr( $live_public_key ); ?>" spellcheck="false">
						</td>
					</tr>
					</tbody>
				</table>

				<?php do_action( 'ht_payjp_for_kintone_after_setting_page' ); ?>

				<p class="submit">
					<input type="submit" name="get_kintone_fields" class="button-primary"
						value="<?php echo esc_attr( __( 'Save', 'payjp-for-kintone' ) ); ?>"/>
				</p>
			</form>
		</div>


		<?php
	}

	/**
	 * 管理画面用のJSとCSSの読み込み.
	 */
	public function ht_payjpforkintone_admin_enqueue_scripts() {

		wp_enqueue_script(
			'jquery-ui',
			HT_PAY_JP_FOR_KINTONE_URL . '/lib/jquery-ui/jquery-ui.min.js',
			array( 'jquery' ),
			date(
				'YmdGis',
				filemtime( HT_PAY_JP_FOR_KINTONE_PATH . '/lib/jquery-ui/jquery-ui.min.js' )
			),
			true
		);
		wp_enqueue_style(
			'jquery-ui-css',
			HT_PAY_JP_FOR_KINTONE_URL . '/lib/jquery-ui/jquery-ui.min.css',
			array(),
			date(
				'YmdGis',
				filemtime( HT_PAY_JP_FOR_KINTONE_PATH . '/lib/jquery-ui/jquery-ui.min.css' )
			)
		);

		wp_enqueue_script(
			'jquery-chosen',
			HT_PAY_JP_FOR_KINTONE_URL . '/lib/chosen/chosen.jquery.min.js',
			array( 'jquery' ),
			date(
				'YmdGis',
				filemtime( HT_PAY_JP_FOR_KINTONE_PATH . '/lib/chosen/chosen.jquery.min.js' )
			),
			true
		);

		wp_enqueue_style(
			'jquery-chosen-css',
			HT_PAY_JP_FOR_KINTONE_URL . '/lib/chosen/chosen.min.css',
			array(),
			date(
				'YmdGis',
				filemtime( HT_PAY_JP_FOR_KINTONE_PATH . '/lib/chosen/chosen.min.css' )
			)
		);

		wp_enqueue_script(
			'payjpforkintone-main-js',
			HT_PAY_JP_FOR_KINTONE_URL . '/assets/js/admin.js',
			array( 'jquery' ),
			date(
				'YmdGis',
				filemtime( HT_PAY_JP_FOR_KINTONE_PATH . '/assets/js/admin.js' )
			),
			true
		);

		wp_enqueue_style(
			'payjpforkintone-admin-css',
			HT_PAY_JP_FOR_KINTONE_URL . '/assets/css/admin.css',
			array(),
			date(
				'YmdGis',
				filemtime( HT_PAY_JP_FOR_KINTONE_PATH . '/assets/css/admin.css' )
			)
		);

		do_action( 'ht_payjp_for_kintone_after_admin_enqueue_scripts' );
	}

	/**
	 * CF7の設定画面にPAY.JP for kintone用のパネルを追加する.
	 *
	 * @param array $panels .
	 *
	 * @return array .
	 */
	public function ht_payjpforkintone_editor_panels( $panels ) {

		$panels['payjpforkintone-panel'] = array(
			'title'    => 'PAY.JP',
			'callback' => array( $this, 'ht_payjpforkintone_panel_handler' ),
		);

		return $panels;
	}

	/**
	 * PAY.JP for kintoneタブの画面を作成する.
	 *
	 * @param WPCF7_ContactForm $post .
	 */
	public function ht_payjpforkintone_panel_handler( $post ) {

		// メールタグ取得.
		$mailtags = $post->collect_mail_tags();

		$payjpforkintone_setting_data = get_post_meta( $post->id(), '_ht_payjpforkintone_setting_data', true );

		$payjpforkintone_setting_data = wp_parse_args(
			$payjpforkintone_setting_data,
			array(
				'payjpforkintone-enabled' => 'disable',
				'live-enabled'            => false,
				'create-customer'         => '',
			)
		);

		$payjpforkintone_enabled = $payjpforkintone_setting_data['payjpforkintone-enabled'];

		$payjpforkintone_language = 'ja';
		if ( isset( $payjpforkintone_setting_data['payjpforkintone-language'] ) && $payjpforkintone_setting_data['payjpforkintone-language'] !== '' ) {
			$payjpforkintone_language = $payjpforkintone_setting_data['payjpforkintone-language'];
		}

		$subscription_enabled = 'checkout';
		$subscription_enabled = apply_filters( 'ht_payjp_for_kintone_admin_subscription_enabled', $subscription_enabled, $post );
		$payjp_plan_id        = '';
		$payjp_plan_id        = apply_filters( 'ht_payjp_for_kintone_admin_payjp_plan_id', $payjp_plan_id, $post );

		$payjp_fixed_subscription_month = '';
		$payjp_fixed_subscription_month = apply_filters( 'ht_payjp_for_kintone_admin_payjp_fixed_subscription_month', $payjp_fixed_subscription_month, $post );

		$payjp_fixed_subscription_time = '';
		$payjp_fixed_subscription_time = apply_filters( 'ht_payjp_for_kintone_admin_payjp_fixed_subscription_time', $payjp_fixed_subscription_time, $post );

		$kintone_fieldcode_for_payjp_subscription_plan_id = '';
		$kintone_fieldcode_for_payjp_subscription_plan_id = apply_filters( 'ht_payjp_for_kintone_admin_kintone_fieldcode_for_payjp_subscription_plan_id', $kintone_fieldcode_for_payjp_subscription_plan_id, $post );
		$kintone_fieldcode_for_payjp_subscription_amount  = '';
		$kintone_fieldcode_for_payjp_subscription_amount  = apply_filters( 'ht_payjp_for_kintone_admin_kintone_fieldcode_for_payjp_subscription_amount', $kintone_fieldcode_for_payjp_subscription_amount, $post );
		$kintone_fieldcode_for_payjp_customer_id          = '';
		$kintone_fieldcode_for_payjp_customer_id          = apply_filters( 'ht_payjp_for_kintone_admin_kintone_fieldcode_for_payjp_customer_id', $kintone_fieldcode_for_payjp_customer_id, $post );
		$kintone_fieldcode_for_payjp_subscription_id      = '';
		$kintone_fieldcode_for_payjp_subscription_id      = apply_filters( 'ht_payjp_for_kintone_admin_kintone_fieldcode_for_payjp_subscription_id', $kintone_fieldcode_for_payjp_subscription_id, $post );

		$live_enabled = '';
		if ( isset( $payjpforkintone_setting_data['live-enabled'] ) ) {
			$live_enabled = $payjpforkintone_setting_data['live-enabled'];
		}

		$amount_cf7_mailtag = '';
		if ( isset( $payjpforkintone_setting_data['amount-cf7-mailtag'] ) ) {
			$amount_cf7_mailtag = $payjpforkintone_setting_data['amount-cf7-mailtag'];
		}

		$description_cf7_mailtag = '';
		if ( isset( $payjpforkintone_setting_data['description-cf7-mailtag'] ) ) {
			$description_cf7_mailtag = $payjpforkintone_setting_data['description-cf7-mailtag'];
		}

		$create_customer = '';
		if ( isset( $payjpforkintone_setting_data['create-customer'] ) ) {
			$create_customer = $payjpforkintone_setting_data['create-customer'];
		}

		?>

		<h2><?php esc_html_e( 'Setting PAY.JP for kintone', 'payjp-for-kintone' ); ?></h2>
		<div id="payjpforkintone-disabled-blocked" class="field-wrap field-wrap-use-external-url">
			<fieldset>
				<label for="payjpforkintone-disabled">
					<?php esc_html_e( 'Disable', 'payjp-for-kintone' ); ?>
				</label>
				<input type="radio" name="ht_payjpforkintone_setting_data[payjpforkintone-enabled]" id="payjpforkintone-disabled" value="disable"<?php checked( $payjpforkintone_enabled, 'disable' ); ?>>
				<label for="payjpforkintone-enabled">
					<?php esc_html_e( 'Enable', 'payjp-for-kintone' ); ?>
				</label>
				<input type="radio" name="ht_payjpforkintone_setting_data[payjpforkintone-enabled]" id="payjpforkintone-enabled" value="enable"<?php checked( $payjpforkintone_enabled, 'enable' ); ?>>
			</fieldset>
		</div>

		<div id="js-payjpforkintone-enabled-block">

			<?php if ( ! $this->check_setting_payjp_key() ) : ?>
				<div class="warning-message">
					Set the PAY.JP key. ->
					<a href="<?php echo esc_url( admin_url( '/admin.php?page=htpayjpforkintone' ) ); ?>">
						<?php echo esc_url( admin_url( '/admin.php?page=htpayjpforkintone' ) ); ?>
					</a>
				</div>
			<?php endif; ?>

			<div id="payjpforkintone-language-blocked" class="field-wrap field-wrap-use-external-url">
				<lable>■ <?php esc_html_e( 'Setting the language to display on the payment dialog box.', 'payjp-for-kintone' ); ?></lable>
				<fieldset>
					<label for="payjpforkintone-japanese">
						<?php esc_html_e( 'Japanese', 'payjp-for-kintone' ); ?>
					</label>
					<input type="radio" name="ht_payjpforkintone_setting_data[payjpforkintone-language]" id="payjpforkintone-japanese" value="ja" <?php checked( $payjpforkintone_language, 'ja' ); ?>>
					<label for="payjpforkintone-english">
						<?php esc_html_e( 'English', 'payjp-for-kintone' ); ?>
					</label>
					<input type="radio" name="ht_payjpforkintone_setting_data[payjpforkintone-language]" id="payjpforkintone-english" value="en" <?php checked( $payjpforkintone_language, 'en' ); ?>>
				</fieldset>
			</div>

			<div class="field-wrap field-wrap-use-external-url">
				<lable>■ <?php esc_html_e( 'Setting live mode.', 'payjp-for-kintone' ); ?></lable>
				<fieldset>
					<label for="live-enabled"><?php esc_html_e( 'Enable Live', 'payjp-for-kintone' ); ?></label>
					<input
						type="checkbox"
						name="ht_payjpforkintone_setting_data[live-enabled]"
						id="live-enabled"
						value="enable"
						<?php checked( $live_enabled, 'enable' ); ?>
					>
				</fieldset>
			</div>

			<div class="field-wrap field-wrap-use-external-url">
				<lable>■ <?php esc_html_e( 'Setting customer creation.', 'payjp-for-kintone' ); ?></lable>
				<fieldset>
					<label for="create-customer"><?php esc_html_e( 'Create Customer', 'payjp-for-kintone' ); ?></label>
					<input
						type="checkbox"
						name="ht_payjpforkintone_setting_data[create-customer]"
						id="create-customer"
						value="enable"
						<?php checked( $create_customer, 'enable' ); ?>
					>
				</fieldset>
			</div>

			<div class="field-wrap field-wrap-use-external-url">
				<fieldset>
					<label for="payment-type-checkout"><?php esc_html_e( 'Checkout', 'payjp-for-kintone' ); ?></label>
					<input
						type="radio"
						name="ht_payjpforkintone_setting_data[payment-type]"
						id="payment-type-checkout"
						value="checkout"
						<?php checked( $subscription_enabled, 'checkout' ); ?>
					>
					<label for="payment-type-subscription"><?php esc_html_e( 'Subscription', 'payjp-for-kintone' ); ?></label>
					<input
						type="radio"
						name="ht_payjpforkintone_setting_data[payment-type]"
						id="payment-type-subscription"
						value="subscription"
						<?php checked( $subscription_enabled, 'subscription' ); ?>
					>

				</fieldset>
			</div>
			<div class="field-wrap field-wrap-use-external-url">
				<fieldset>
					<label for="payjp-plan-id">
						<?php esc_html_e( 'PAY.JP\'s Plan ID', 'payjp-for-kintone' ); ?>
					</label><br/>
					<input type="text" id="payjp-plan-id" value="<?php echo esc_attr( $payjp_plan_id ); ?>" name="ht_payjpforkintone_setting_data[payjp-plan-id]">
				</fieldset>
			</div>

			<div class="field-wrap field-wrap-use-external-url">
				<fieldset>
					<label for="payjp-fixed-subscription-date">
						<?php esc_html_e( 'Fixed Subscription datetime', 'payjp-for-kintone' ); ?>
					</label><br/>
					<select name="ht_payjpforkintone_setting_data[payjp-fixed-subscription-month]" id="payjp-fixed-subscription-month">
						<option value="">month / day</option>
						<?php for ( $month = 1; $month <= 12; $month++ ) : ?>
							<option value="<?php printf( '%02d', $month ); ?>-01" <?php selected( sprintf( '%02d', $month ) . '-01', $payjp_fixed_subscription_month ); ?>><?php printf( '%02d', $month ); ?>/01</option>
						<?php endfor; ?>
					</select>

					<select name="ht_payjpforkintone_setting_data[payjp-fixed-subscription-time]" id="payjp-fixed-subscription-time">
						<option value="">time</option>
						<?php for ( $hour = 0; $hour <= 23; $hour++ ) : ?>
							<option value="<?php printf( '%02d', $hour ); ?>" <?php selected( sprintf( '%02d', $hour ), $payjp_fixed_subscription_time ); ?>><?php printf( '%02d', $hour ); ?>:00</option>
						<?php endfor; ?>
					</select>
				</fieldset>
			</div>

			<div class="field-wrap field-wrap-use-external-url">
				<fieldset>
					<label for="amount-cf7-mailtag">
						<?php esc_html_e( 'Select amount of CF7 mailtag', 'payjp-for-kintone' ); ?>
					</label><br/>

					<select
						name="ht_payjpforkintone_setting_data[amount-cf7-mailtag]"
						data-placeholder="<?php esc_html_e( 'Select amount of CF7 mailtag', 'payjp-for-kintone' ); ?>"
						class="chosen-select"
						style="width:350px;"
						id="amount-cf7-mailtag"
					>
						<option value=""></option>
						<?php foreach ( $mailtags as $mailtag ) : ?>
							<option
								value="<?php echo esc_textarea( $mailtag ); ?>"
								<?php selected( $amount_cf7_mailtag, $mailtag ); ?>
							>
								<?php echo esc_textarea( $mailtag ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</fieldset>
				<fieldset>
					<label for="description-cf7-mailtag">
						<?php esc_html_e( 'Select description of CF7 mailtag', 'payjp-for-kintone' ); ?>
					</label><br/>

					<select
						name="ht_payjpforkintone_setting_data[description-cf7-mailtag]"
						data-placeholder="<?php esc_html_e( 'Select description of CF7 mailtag', 'payjp-for-kintone' ); ?>"
						class="chosen-select"
						style="width:350px;"
						id="description-cf7-mailtag"
					>
						<option value=""></option>
						<?php foreach ( $mailtags as $mailtag ) : ?>
							<option
								value="<?php echo esc_textarea( $mailtag ); ?>"
								<?php selected( $description_cf7_mailtag, $mailtag ); ?>
							>
								<?php echo esc_textarea( $mailtag ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</fieldset>
			</div>

			<?php esc_html_e( 'Paste the following shortcode of Contact form 7 on form of Contact form 7', 'payjp-for-kintone' ); ?>
			<span class="shortcode wp-ui-highlight">
					<input type="text" id="payjpforkintone-shortcode" onfocus="this.select();" readonly="readonly" class="large-text code" value="[ht_payjp_for_kintone]">
			</span>

			<?php do_action( 'add_explain_ht_payjp_for_kintone_shortcode' ); ?>

			<img src="<?php echo esc_attr( HT_PAY_JP_FOR_KINTONE_URL . '/assets/images/admin-shortcode.jpg' ); ?>" alt="">

		</div>
		<?php
	}

	/**
	 * PAY.JPのキーが保存されているか確認する.
	 *
	 * @return boolean
	 */
	private function check_setting_payjp_key() {

		$pay_jp_for_kintone_test_public_key = get_option( 'ht_pay_jp_for_kintone_test_public_key' );
		$pay_jp_for_kintone_test_secret_key = get_option( 'ht_pay_jp_for_kintone_test_secret_key' );
		$pay_jp_for_kintone_live_public_key = get_option( 'ht_pay_jp_for_kintone_live_public_key' );
		$pay_jp_for_kintone_live_secret_key = get_option( 'ht_pay_jp_for_kintone_live_secret_key' );

		if ( empty( $pay_jp_for_kintone_test_public_key ) || empty( $pay_jp_for_kintone_test_secret_key ) || empty( $pay_jp_for_kintone_live_public_key ) || empty( $pay_jp_for_kintone_live_secret_key ) ) {
			return false;
		}

		return true;
	}

	/**
	 * PAY.JPタブで設定した情報を保存できるようにCF7のPropertiesに追加しておく。
	 *
	 * @param array             $properties .
	 * @param WPCF7_ContactForm $contact_form .
	 *
	 * @return array An array of image URLs.
	 */
	public function ht_payjpforkintone_add_properties_to_contact_form_properties( $properties, $contact_form ) {

		$properties = wp_parse_args(
			$properties,
			array(
				'ht_payjpforkintone_setting_data' => array(),
			)
		);

		return $properties;
	}


	/**
	 * 管理画面のメニュー作成
	 */
	public function admin_menu() {
		$page = add_submenu_page(
			'options-general.php',
			'HT PAY.JP for kintone',
			'HT PAY.JP for kintone',
			'manage_options',
			'ht-payjp-for-kintone',
			array(
				$this,
				'admin_setting',
			)
		);
	}

	/**
	 * 設定値の更新.
	 *
	 * Secret Key が空欄でサブミットされた場合は既存値を維持する。
	 * Public Key は空欄でも明示的なクリアとして保存する。
	 * 不正なフォーマットの鍵はエラーを返し、保存しない。
	 *
	 * @return array {
	 *     @type bool     $success エラーなく1件以上保存できたか.
	 *     @type string[] $errors  ユーザーに表示するエラーメッセージ.
	 * }
	 */
	private function update() {

		$result = array(
			'success' => false,
			'errors'  => array(),
		);

		if ( ! check_admin_referer( $this->nonce ) ) {
			return $result;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return $result;
		}

		// Public Key は空でも保存（明示的なクリアを許可）.
		$public_keys = array(
			'test-public-key' => array(
				'option' => 'ht_pay_jp_for_kintone_test_public_key',
				'prefix' => 'pk_test_',
				'label'  => __( 'Test Public Key', 'payjp-for-kintone' ),
			),
			'live-public-key' => array(
				'option' => 'ht_pay_jp_for_kintone_live_public_key',
				'prefix' => 'pk_live_',
				'label'  => __( 'Live Public Key', 'payjp-for-kintone' ),
			),
		);
		foreach ( $public_keys as $field => $meta ) {
			if ( ! isset( $_POST[ $field ] ) ) {
				continue;
			}
			$input = sanitize_text_field( wp_unslash( $_POST[ $field ] ) );
			if ( '' !== $input && ! self::is_valid_payjp_key( $input, $meta['prefix'] ) ) {
				/* translators: 1: フィールド名, 2: 期待されるプレフィックス */
				$result['errors'][] = sprintf( __( '%1$s の形式が不正です（%2$s で始まる必要があります）。', 'payjp-for-kintone' ), $meta['label'], $meta['prefix'] );
				continue;
			}
			self::update_key_option( $meta['option'], $input );
		}

		// Secret Key は空欄なら既存値を維持。入力がある場合のみ検証して保存。.
		$secret_keys = array(
			'test-secret-key' => array(
				'option' => 'ht_pay_jp_for_kintone_test_secret_key',
				'prefix' => 'sk_test_',
				'label'  => __( 'Test Secret Key', 'payjp-for-kintone' ),
			),
			'live-secret-key' => array(
				'option' => 'ht_pay_jp_for_kintone_live_secret_key',
				'prefix' => 'sk_live_',
				'label'  => __( 'Live Secret Key', 'payjp-for-kintone' ),
			),
		);
		foreach ( $secret_keys as $field => $meta ) {
			if ( ! isset( $_POST[ $field ] ) ) {
				continue;
			}
			$input = sanitize_text_field( wp_unslash( $_POST[ $field ] ) );
			if ( '' === $input ) {
				continue; // 既存値維持.
			}
			if ( ! self::is_valid_payjp_key( $input, $meta['prefix'] ) ) {
				/* translators: 1: フィールド名, 2: 期待されるプレフィックス */
				$result['errors'][] = sprintf( __( '%1$s の形式が不正です（%2$s で始まる必要があります）。', 'payjp-for-kintone' ), $meta['label'], $meta['prefix'] );
				continue;
			}
			self::update_key_option( $meta['option'], $input );
		}

		// 4本すべての autoload を 'no' に揃える（毎リクエストの自動展開を回避）.
		// 値が空欄サブミットで update_key_option を通らなかった場合でも確実に切り替える.
		if ( function_exists( 'wp_set_options_autoload' ) ) {
			wp_set_options_autoload(
				array(
					'ht_pay_jp_for_kintone_test_secret_key',
					'ht_pay_jp_for_kintone_test_public_key',
					'ht_pay_jp_for_kintone_live_secret_key',
					'ht_pay_jp_for_kintone_live_public_key',
				),
				'no'
			);
		}

		do_action( 'ht_payjp_for_kintone_admin_setting_update' );

		$result['success'] = empty( $result['errors'] );
		return $result;
	}

	/**
	 * PAY.JP の鍵フォーマットを検証する.
	 *
	 * @param string $key             検証対象の鍵.
	 * @param string $expected_prefix 期待されるプレフィックス（例: sk_test_）.
	 *
	 * @return bool
	 */
	private static function is_valid_payjp_key( $key, $expected_prefix ) {
		if ( '' === (string) $key ) {
			return true;
		}
		return 0 === strpos( $key, $expected_prefix )
			&& (bool) preg_match( '/^[A-Za-z0-9_]+$/', $key );
	}

	/**
	 * Secret Key 表示用にマスク化する（下4桁のみ平文）.
	 *
	 * @param string $key マスク対象の鍵.
	 *
	 * @return string
	 */
	private static function mask_secret_key( $key ) {
		$key = (string) $key;
		if ( '' === $key ) {
			return '';
		}
		$len = strlen( $key );
		if ( $len <= 4 ) {
			return str_repeat( '•', $len );
		}
		return str_repeat( '•', max( 8, $len - 4 ) ) . substr( $key, -4 );
	}

	/**
	 * オプションを autoload=no で保存する.
	 *
	 * Secret Key を含むため、毎リクエストでメモリに展開しないようにする。
	 *
	 * @param string $name  オプション名.
	 * @param string $value 保存値.
	 */
	private static function update_key_option( $name, $value ) {
		if ( false === get_option( $name, false ) ) {
			add_option( $name, $value, '', 'no' );
			return;
		}
		update_option( $name, $value );
		// 値が変わらない場合でも autoload を強制で 'no' に切り替える（WP 6.4+）.
		if ( function_exists( 'wp_set_option_autoload' ) ) {
			wp_set_option_autoload( $name, 'no' );
		}
	}
}
