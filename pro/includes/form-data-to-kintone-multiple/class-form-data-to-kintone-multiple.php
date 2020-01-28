<?php

class KintoneFormMultipleApp {
	public function __construct() {
		add_action( 'kintone_form_setting_panel_after', array( $this, 'kintone_form_setting_panel_after' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'kintone_form_do_admin_enqueue_scripts' ) );
	}

	public function kintone_form_setting_panel_after() {
		ob_start(); // 記録開始
		?>
		<table class="template row" style="margin-bottom: 30px; border-top: 6px solid #ccc; width: 100%;">
			<tr>
				<td valign="top" style="padding: 10px 0px;">
					APP ID:<input type="text" id="kintone-form-appid" name="kintone_setting_data[app_datas][{{row-count-placeholder}}][appid]" class="small-text" size="70" value=""/>
					Api Token:<input type="text" id="kintone-form-token" name="kintone_setting_data[app_datas][{{row-count-placeholder}}][token]" class="regular-text" size="70" value=""/>
					<input type="submit" class="button-primary" name="get-kintone-data" value="GET">
				</td>
				<td width="10%"><span class="remove button">Remove</span></td>
			</tr>
		</table>
		<?php
		$html = ob_get_contents();
		ob_end_clean();
		echo $html;
	}

	public function kintone_form_do_admin_enqueue_scripts() {

		wp_enqueue_script(
			'repeatable-fields',
			HT_PAY_JP_FOR_KINTONE_URL . '/pro/includes/form-data-to-kintone-multiple/asset/js/repeatable-fields/repeatable-fields.js',
			array( 'jquery' ),
			filemtime( dirname( __FILE__ ) . '/asset/js/repeatable-fields/repeatable-fields.js' ),
			true
		);

		wp_enqueue_script(
			'kintone-form',
			HT_PAY_JP_FOR_KINTONE_URL . '/pro/includes/form-data-to-kintone-multiple/asset/js/scripts.js',
			array( 'jquery' ),
			filemtime( dirname( __FILE__ ) . '/asset/js/scripts.js' ),
			true
		);

	}
}

new KintoneFormMultipleApp();
