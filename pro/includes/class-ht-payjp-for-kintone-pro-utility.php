<?php

class HT_Payjp_For_Kintone_Pro_Utility {

	public static function get_kintone_field_code_of_payjp_information( $target_payjp_id, $appid, $contact_form ) {

		$kintone_setting_data = $contact_form->prop( 'kintone_setting_data' );
		foreach ( $kintone_setting_data['app_datas'] as $appdata ) {
			if ( $appdata['appid'] === $appid ) {
				$target_app_data = $appdata;
				break;
			}
		}
		$kintone_data_for_post = self::get_data_for_post( $target_app_data );
		if ( isset( $kintone_data_for_post['setting'] ) ) {
			foreach ( $kintone_data_for_post['setting'] as $kintone_fieldcode => $cf7_mail_tag ) {
				if ( $target_payjp_id === $cf7_mail_tag ) {
					return $kintone_fieldcode;
				}
			}
		}

		return '';
	}

	public static function get_data_for_post( $appdata ) {

		$data['setting'] = array();

		if ( isset( $appdata['setting_original_cf7tag_name'] ) && ! empty( $appdata['setting_original_cf7tag_name'] ) ) {

			foreach ( $appdata['setting_original_cf7tag_name'] as $key => $value ) {
				if ( $value ) {
					$data['setting'][ $key ] = $value;
				} else {
					if ( isset( $appdata['setting'][ $key ] ) ) {
						$data['setting'][ $key ] = $appdata['setting'][ $key ];
					}
				}
			}
		} else {
			if ( isset( $appdata['setting'] ) ) {
				return $appdata['setting'];
			}
		}

		return $data;
	}
}
