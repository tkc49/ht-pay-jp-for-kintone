<?php

class HT_Payjp_For_Kintone_Pro_Target_Contact_Form {

	private $plan_id;
	private $contact_form;

	public function __construct( $_plan_id ) {
		$this->plan_id = $_plan_id;
	}


	public function check_my_plan_subscription_for_ht_payjp_for_kintone() {

		$contact_form_items = WPCF7_ContactForm::find();

		foreach ( $contact_form_items as $contact_form_item ) {
			$payjpforkintone_setting_data = get_post_meta( $contact_form_item->id(), '_ht_payjpforkintone_setting_data', true );
			if ( $this->plan_id === $payjpforkintone_setting_data['payjp-plan-id'] ) {
				// ht Pay.JP for kintone で設定しているPLAN IDと同じものがあれば課金対象
				$this->contact_form = $contact_form_item;

				return true;
			}
		}

		return false;
	}

	public function get_target_contact_form() {
		return $this->contact_form;
	}
}
