<?php
/**
 * Admin の Classファイル
 *
 * @package Payjp_For_Kintone
 * @version 1.0.0
 */

/**
 * Admin.
 */
class Admin {

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
		add_filter( 'wpcf7_editor_panels', array( $this, 'ht_payjpforkintone_editor_panels' ) );
		add_filter(
			'wpcf7_contact_form_properties',
			array( $this, 'ht_payjpforkintone_add_properties_to_contact_form_properties' ),
			10,
			2
		);

		// 保存したときに実行.
		add_action( 'wpcf7_save_contact_form', array( $this, 'ht_payjpforkintone_save_contact_form' ), 10, 3 );

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

			if ( $this->update() ) {
				echo '<div class="updated notice is-dismissible"><p><strong>Success</strong></p></div>';
			} else {
				echo '<div class="error notice is-dismissible"><p><strong>Error</strong></p></div>';
			}
		}

		$test_secret_key = get_option( 'ht_pay_jp_for_kintone_test_secret_key' );
		$test_public_key = get_option( 'ht_pay_jp_for_kintone_test_public_key' );
		$live_secret_key = get_option( 'ht_pay_jp_for_kintone_live_secret_key' );
		$live_public_key = get_option( 'ht_pay_jp_for_kintone_live_public_key' );

		?>
		<div class="wrap">
			<h2>PAY.JP for kintone Settings</h2>
			<form id="payjp-for-kintone-form" method="post" action="">

				<?php wp_nonce_field( $this->nonce ); ?>

				<table class="form-table">
					<tbody>
					<tr valign="top">
						<th scope="row">
							<label for="add_text">
								<?php esc_html_e( 'Test Secret Key', 'payjp-for-kintone' ); ?>
							</label>
						</th>
						<td>
							<input type="text" name="test-secret-key" class="regular-text"
								value="<?php echo esc_attr( $test_secret_key ); ?>">
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="add_text">
								<?php esc_html_e( 'Test Public Key', 'payjp-for-kintone' ); ?>
							</label>
						</th>
						<td>
							<input type="text" name="test-public-key" class="regular-text"
								value="<?php echo esc_attr( $test_public_key ); ?>">
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="add_text">
								<?php esc_html_e( 'Live Secret Key', 'payjp-for-kintone' ); ?>
							</label>
						</th>
						<td>
							<input type="text" name="live-secret-key" class="regular-text"
								value="<?php echo esc_attr( $live_secret_key ); ?>">
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="add_text">
								<?php esc_html_e( 'Live Public Key', 'payjp-for-kintone' ); ?>
							</label>
						</th>
						<td>
							<input type="text" name="live-public-key" class="regular-text"
								value="<?php echo esc_attr( $live_public_key ); ?>">
						</td>
					</tr>
					</tbody>
				</table>
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
			)
		);

		$payjpforkintone_enabled = $payjpforkintone_setting_data['payjpforkintone-enabled'];

		$live_enabled = '';
		if ( isset( $payjpforkintone_setting_data['live-enabled'] ) ) {
			$live_enabled = $payjpforkintone_setting_data['live-enabled'];
		}

		$amount_cf7_mailtag = '';
		if ( isset( $payjpforkintone_setting_data['amount-cf7-mailtag'] ) ) {
			$amount_cf7_mailtag = $payjpforkintone_setting_data['amount-cf7-mailtag'];
		}

		$kintone_enabled = '';
		if ( isset( $payjpforkintone_setting_data['kintone-enabled'] ) ) {
			$kintone_enabled = $payjpforkintone_setting_data['kintone-enabled'];
		}

		$kintone_fieldcode_for_payjp_billing_id = '';
		if ( isset( $payjpforkintone_setting_data['kintone-fieldcode-for-payjp-billing-id'] ) ) {
			$kintone_fieldcode_for_payjp_billing_id = $payjpforkintone_setting_data['kintone-fieldcode-for-payjp-billing-id'];
		}

		?>

		<h2><?php esc_html_e( 'Setting PAY.JP for kintone', 'payjp-for-kintone' ); ?></h2>
		<div id="payjpforkintone-disabled-blocked" class="field-wrap field-wrap-use-external-url">
			<fieldset>
				<label for="payjpforkintone-disabled">
					<?php esc_html_e( 'Disable', 'payjp-for-kintone' ); ?>
				</label>
				<input type="radio"
					name="ht_payjpforkintone_setting_data[payjpforkintone-enabled]"
					id="payjpforkintone-disabled"
					value="disable"
					<?php checked( $payjpforkintone_enabled, 'disable' ); ?>
				>
				<label for="payjpforkintone-enabled">
					<?php esc_html_e( 'Enable', 'payjp-for-kintone' ); ?>
				</label>
				<input type="radio"
					name="ht_payjpforkintone_setting_data[payjpforkintone-enabled]"
					id="payjpforkintone-enabled"
					value="enable"
					<?php checked( $payjpforkintone_enabled, 'enable' ); ?>
				>
			</fieldset>

		</div>

		<div id="js-payjpforkintone-enabled-block">

			<?php if ( ! $this->check_setting_payjp_key() ) : ?>
				<div class="warning-message">
					Set the PAY.JP key. ->
					<a href="<?php echo esc_url( admin_url( '/admin.php?page=payjpforkintone' ) ); ?>">
						<?php echo esc_url( admin_url( '/admin.php?page=payjpforkintone' ) ); ?>
					</a>
				</div>
			<?php endif; ?>

			<div class="field-wrap field-wrap-use-external-url">

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
			</div>

			<?php
			esc_html_e(
				'Paste the following shortcode of Contact form 7 on form of Contact form 7',
				'payjp-for-kintone'
			);
			?>
			<span class="shortcode wp-ui-highlight">
					<input type="text" id="payjpforkintone-shortcode" onfocus="this.select();" readonly="readonly" class="large-text code" value="[ht_payjp_for_kintone]">
			</span>

			<span class="shortcode wp-ui-highlight">
					<input type="text" id="payjpforkintone-shortcode-of-payment-id" onfocus="this.select();" readonly="readonly" class="large-text code" value="[hidden payjp-charged-id]">
			</span>

			<img src="<?php echo esc_attr(
				HT_PAY_JP_FOR_KINTONE_URL . '/assets/images/admin-shortcode.jpg'
			); ?>" alt="">


			<div class="setting-kintone-block">

				<div class="field-wrap field-wrap-use-external-url">

					<fieldset>
						<label for="kintone-enabled">
							<?php esc_html_e( 'Enable kintone', 'payjp-for-kintone' ); ?>
						</label>
						<input
							type="checkbox"
							name="ht_payjpforkintone_setting_data[kintone-enabled]"
							id="kintone-enabled"
							value="enable"
							<?php checked( $kintone_enabled, 'enable' ); ?>
						>
					</fieldset>
				</div>

				<div class="field-wrap field-wrap-use-external-url">
					<fieldset>
						<label for="payjp-billing-id">
							<?php esc_html_e( 'Select PAY.JP Billing ID of CF7 mailtag', 'payjp-for-kintone' ); ?>
						</label><br/>
						<input type="text" id="payjp-billing-id"
							value="<?php echo esc_attr( $kintone_fieldcode_for_payjp_billing_id ); ?>"
							name="ht_payjpforkintone_setting_data[kintone-fieldcode-for-payjp-billing-id]">
					</fieldset>
				</div>
			</div>
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
	 * Update.
	 */
	private function update() {

		if ( ! check_admin_referer( $this->nonce ) ) {
			return false;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$safe_test_public_key = '';
		if ( isset( $_POST['test-public-key'] ) ) {
			$test_public_key      = (string) filter_input( INPUT_POST, 'test-public-key' );
			$safe_test_public_key = sanitize_text_field( $test_public_key );
		}
		update_option( 'ht_pay_jp_for_kintone_test_public_key', $safe_test_public_key );

		$safe_test_secret_key = '';
		if ( isset( $_POST['test-secret-key'] ) ) {
			$test_secret_key      = (string) filter_input( INPUT_POST, 'test-secret-key' );
			$safe_test_secret_key = sanitize_text_field( $test_secret_key );
		}
		update_option( 'ht_pay_jp_for_kintone_test_secret_key', $safe_test_secret_key );

		$safe_live_public_key = '';
		if ( isset( $_POST['live-public-key'] ) ) {
			$live_public_key      = (string) filter_input( INPUT_POST, 'live-public-key' );
			$safe_live_public_key = sanitize_text_field( $live_public_key );
		}
		update_option( 'ht_pay_jp_for_kintone_live_public_key', $safe_live_public_key );

		$safe_live_secret_key = '';
		if ( isset( $_POST['live-public-key'] ) ) {
			$live_secret_key      = (string) filter_input( INPUT_POST, 'live-secret-key' );
			$safe_live_secret_key = sanitize_text_field( $live_secret_key );
		}
		update_option( 'ht_pay_jp_for_kintone_live_secret_key', $safe_live_secret_key );

		return true;
	}
}

