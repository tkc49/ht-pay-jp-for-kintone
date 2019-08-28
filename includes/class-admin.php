<?php
/**
 * Admin の Classファイル
 *
 * @package Pay_Jp_For_Kintone
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
	private $nonce = 'pay-jp-for-kintone-admin-setting-page';

	/**
	 * Admin constructor.
	 */
	public function __construct() {
//		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'payjpforkintone_admin_enqueue_scripts' ) );
		add_action( 'admin_menu', array( $this, 'payjpforkintone_admin_add_page' ) );

		// Add PAY.JP setting panel to CF7.
		add_filter( 'wpcf7_editor_panels', array( $this, 'payjpforkintone_editor_panels' ) );
		add_filter(
			'wpcf7_contact_form_properties',
			array( $this, 'payjpforkintone_add_properties_to_contact_form_properties' ),
			10,
			2
		);

		// 保存したときに実行.
		add_action( 'wpcf7_save_contact_form', array( $this, 'payjpforkintone_save_contact_form' ), 10, 3 );

	}

	/**
	 * CF7で保存したとき.
	 *
	 * @param WPCF7_ContactForm $contact_form .
	 * @param array             $args .
	 * @param string            $context .
	 */
	public function payjpforkintone_save_contact_form( $contact_form, $args, $context ) {
		$properties                                 = array();
		$properties['payjpforkintone_setting_data'] = $args['payjpforkintone_setting_data'];
		$contact_form->set_properties( $properties );
	}

	/**
	 * CF7のメニューにオプションページのメニューを追加する.
	 */
	public function payjpforkintone_admin_add_page() {
		add_submenu_page(
			'wpcf7',
			'PAY.JP for kintone',
			'PAY.JP for kintone',
			WPCF7_ADMIN_READ_WRITE_CAPABILITY,
			'payjpforkintone',
			array( $this, 'payjpforkintone_options_page_handler' )
		);
	}

	/**
	 * PAY.JP for kintone オプションページ作成.
	 */
	public function payjpforkintone_options_page_handler() {

		if ( ! empty( $_POST ) && check_admin_referer( $this->nonce ) ) {

			if ( $this->update() ) {
				echo '<div class="updated notice is-dismissible"><p><strong>Success</strong></p></div>';
			} else {
				echo '<div class="error notice is-dismissible"><p><strong>Error</strong></p></div>';
			}
		}

		$test_secret_key = get_option( 'pay_jp_for_kintone_test_secret_key' );
		$test_public_key = get_option( 'pay_jp_for_kintone_test_public_key' );
		$live_secret_key = get_option( 'pay_jp_for_kintone_live_secret_key' );
		$live_public_key = get_option( 'pay_jp_for_kintone_live_public_key' );

		?>
		<div class="wrap">
			<h2>PAY.JP for kintone Settings</h2>
			<form id="pay-jp-for-kintone-form" method="post" action="">

				<?php wp_nonce_field( $this->nonce ); ?>

				<table class="form-table">
					<tbody>
					<tr valign="top">
						<th scope="row">
							<label for="add_text">
								<?php esc_html_e( 'Test Secret Key', 'pay-jp-for-kintone' ); ?>
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
								<?php esc_html_e( 'Test Public Key', 'pay-jp-for-kintone' ); ?>
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
								<?php esc_html_e( 'Live Secret Key', 'pay-jp-for-kintone' ); ?>
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
								<?php esc_html_e( 'Live Public Key', 'pay-jp-for-kintone' ); ?>
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
						value="<?php echo esc_attr( __( 'Save', 'pay-jp-for-kintone' ) ); ?>"/>
				</p>
			</form>
		</div>


		<?php
	}

	/**
	 * 管理画面用のJSとCSSの読み込み.
	 */
	public function payjpforkintone_admin_enqueue_scripts() {

		wp_enqueue_script(
			'jquery-ui',
			PAY_JP_FOR_KINTONE_URL . '/lib/jquery-ui/jquery-ui.min.js',
			array( 'jquery' ),
			date(
				'YmdGis',
				filemtime( PAY_JP_FOR_KINTONE_PATH . '/lib/jquery-ui/jquery-ui.min.js' )
			),
			true
		);
		wp_enqueue_style(
			'jquery-ui-css',
			PAY_JP_FOR_KINTONE_URL . '/lib/jquery-ui/jquery-ui.min.css',
			array(),
			date(
				'YmdGis',
				filemtime( PAY_JP_FOR_KINTONE_PATH . '/lib/jquery-ui/jquery-ui.min.css' )
			)
		);

		wp_enqueue_script(
			'jquery-chosen',
			PAY_JP_FOR_KINTONE_URL . '/lib/chosen/chosen.jquery.min.js',
			array( 'jquery' ),
			date(
				'YmdGis',
				filemtime( PAY_JP_FOR_KINTONE_PATH . '/lib/chosen/chosen.jquery.min.js' )
			),
			true
		);

		wp_enqueue_style(
			'jquery-chosen-css',
			PAY_JP_FOR_KINTONE_URL . '/lib/chosen/chosen.min.css',
			array(),
			date(
				'YmdGis',
				filemtime( PAY_JP_FOR_KINTONE_PATH . '/lib/chosen/chosen.min.css' )
			)
		);

		wp_enqueue_script(
			'payjpforkintone-main-js',
			PAY_JP_FOR_KINTONE_URL . '/assets/js/admin.js',
			array( 'jquery' ),
			date(
				'YmdGis',
				filemtime( PAY_JP_FOR_KINTONE_PATH . '/assets/js/admin.js' )
			),
			true
		);

		wp_enqueue_style(
			'payjpforkintone-admin-css',
			PAY_JP_FOR_KINTONE_URL . '/assets/css/admin.css',
			array(),
			date(
				'YmdGis',
				filemtime( PAY_JP_FOR_KINTONE_PATH . '/assets/css/admin.css' )
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
	public function payjpforkintone_editor_panels( $panels ) {

		$panels['payjpforkintone-panel'] = array(
			'title'    => 'PAY.JP',
			'callback' => array( $this, 'payjpforkintone_panel_handler' ),
		);

		return $panels;
	}

	/**
	 * PAY.JP for kintoneタブの画面を作成する.
	 *
	 * @param WPCF7_ContactForm $post .
	 */
	public function payjpforkintone_panel_handler( $post ) {

		// メールタグ取得.
		$mailtags = $post->collect_mail_tags();

		$payjpforkintone_setting_data = get_post_meta( $post->id(), '_payjpforkintone_setting_data', true );

		$payjpforkintone_setting_data = wp_parse_args(
			$payjpforkintone_setting_data,
			array(
				'payjpforkintone-enabled' => 'disable',
				'live-enabled'            => false,
			)
		);

		$payjpforkintone_enabled = $payjpforkintone_setting_data['payjpforkintone-enabled'];


		$live_enabled      = $payjpforkintone_setting_data['live-enabled'];
		$amout_cf7_mailtag = $payjpforkintone_setting_data['amout-cf7-mailtag'];

		?>

		<h2><?php esc_html_e( 'Setting PAY.JP for kintone', 'pay-jp-for-kintone' ); ?></h2>
		<div id="payjpforkintone-disabled-blocked" class="field-wrap field-wrap-use-external-url">
			<fieldset>
				<label for="payjpforkintone-disabled">
					<?php esc_html_e( 'Disable', 'pay-jp-for-kintone' ); ?>
				</label>
				<input type="radio"
					name="payjpforkintone_setting_data[payjpforkintone-enabled]"
					id="payjpforkintone-disabled"
					value="disable"
					<?php checked( $payjpforkintone_enabled, 'disable' ); ?>
				>
				<label for="payjpforkintone-enabled">
					<?php esc_html_e( 'Enable', 'pay-jp-for-kintone' ); ?>
				</label>
				<input type="radio"
					name="payjpforkintone_setting_data[payjpforkintone-enabled]"
					id="payjpforkintone-enabled"
					value="enable"
					<?php checked( $payjpforkintone_enabled, 'enable' ); ?>
				>
			</fieldset>

		</div>

		<div id="js-payjpforkintone-enabled-block">

			<div class="field-wrap field-wrap-use-external-url">

				<fieldset>
					<label for="live-enabled"><?php esc_html_e( 'Enable Live', 'pay-jp-for-kintone' ); ?></label>
					<input
						type="checkbox"
						name="payjpforkintone_setting_data[live-enabled]"
						id="live-enabled"
						value="enable"
						<?php checked( $live_enabled, 'enable' ); ?>
					>
				</fieldset>
			</div>

			<div class="field-wrap field-wrap-use-external-url">
				<fieldset>
					<label for="amout-cf7-mailtag">
						<?php esc_html_e( 'Select amout of CF7 mailtag', 'pay-jp-for-kintone' ); ?>
					</label><br/>

					<select
						name="payjpforkintone_setting_data[amout-cf7-mailtag]"
						data-placeholder="<?php esc_html_e( 'Select amout of CF7 mailtag', 'pay-jp-for-kintone' ); ?>"
						class="chosen-select"
						style="width:350px;"
						id="amout-cf7-mailtag"
					>
						<option value=""></option>
						<?php foreach ( $mailtags as $mailtag ) : ?>
							<option
								value="<?php echo esc_textarea( $mailtag ); ?>"
								<?php selected( $amout_cf7_mailtag, $mailtag ); ?>
							>
								<?php echo esc_textarea( $mailtag ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</fieldset>
			</div>

		</div>
		<?php
	}

	/**
	 * PAY.JPタブで設定した情報を保存できるようにCF7のPropertiesに追加しておく。
	 *
	 * @param array             $properties .
	 * @param WPCF7_ContactForm $contact_form .
	 *
	 * @return array An array of image URLs.
	 */
	public function payjpforkintone_add_properties_to_contact_form_properties( $properties, $contact_form ) {

		$properties = wp_parse_args(
			$properties,
			array(
				'payjpforkintone_setting_data' => array(),
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
			'PAY.JP for kintone',
			'PAY.JP for kintone',
			'manage_options',
			'pay-jp-for-kintone',
			array(
				$this,
				'admin_setting',
			)
		);
//		登録した $page ハンドルをを使ってスタイルシートの読み込みをフック
//		add_action( 'admin_print_styles-' . $page, array( $this, 'import_kintone_admin_styles' ) );
//		add_action( 'admin_print_scripts-' . $page, array( $this, 'import_kintone_admin_js' ) );
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
		update_option( 'pay_jp_for_kintone_test_public_key', $safe_test_public_key );

		$safe_test_secret_key = '';
		if ( isset( $_POST['test-secret-key'] ) ) {
			$test_secret_key      = (string) filter_input( INPUT_POST, 'test-secret-key' );
			$safe_test_secret_key = sanitize_text_field( $test_secret_key );
		}
		update_option( 'pay_jp_for_kintone_test_secret_key', $safe_test_secret_key );

		$safe_live_public_key = '';
		if ( isset( $_POST['live-public-key'] ) ) {
			$live_public_key      = (string) filter_input( INPUT_POST, 'live-public-key' );
			$safe_live_public_key = sanitize_text_field( $live_public_key );
		}
		update_option( 'pay_jp_for_kintone_live_public_key', $safe_live_public_key );

		$safe_live_secret_key = '';
		if ( isset( $_POST['live-public-key'] ) ) {
			$live_secret_key      = (string) filter_input( INPUT_POST, 'live-secret-key' );
			$safe_live_secret_key = sanitize_text_field( $live_secret_key );
		}
		update_option( 'pay_jp_for_kintone_live_secret_key', $safe_live_secret_key );

		return true;
	}
}

