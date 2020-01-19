// 初期設定.
(
	function( $ ){

		function enabledBlockControle(){

			const enabled = $( '[name="ht_payjpforkintone_setting_data[payjpforkintone-enabled]"]:checked' ).val();

			if ( enabled === 'enable' ) {
				$( '#js-payjpforkintone-enabled-block' ).show();
			} else {
				$( '#js-payjpforkintone-enabled-block' ).hide();
			}

			const kintoneEnabled = $( '[name="ht_payjpforkintone_setting_data[kintone-enabled]"]:checked' ).val();

			if ( kintoneEnabled === 'enable' ) {
				$( '[name="ht_payjpforkintone_setting_data[kintone-fieldcode-for-payjp-billing-id]"]' ).prop( 'disabled', false ).trigger( "chosen:updated" );

			} else {
				$( '[name="ht_payjpforkintone_setting_data[kintone-fieldcode-for-payjp-billing-id]"]' ).prop( 'disabled', true ).trigger( "chosen:updated" );
			}

		}

		function main(){
			$(
				function(){
					$( '[name="ht_payjpforkintone_setting_data[payjpforkintone-enabled]"]' ).checkboxradio();
					$( '[name="ht_payjpforkintone_setting_data[live-enabled]"]' ).checkboxradio();
					$( '[name="ht_payjpforkintone_setting_data[kintone-enabled]"]' ).checkboxradio();
					$( '[name="ht_payjpforkintone_setting_data[subscription-enabled]"]' ).checkboxradio();

					$( '.chosen-select' ).chosen(
						{
							allow_single_deselect: true,
							width                : '100%',
						}
					);

					enabledBlockControle();
				}
			);

			$(
				function(){
					$( '[name="ht_payjpforkintone_setting_data[payjpforkintone-enabled]"]' ).change(
						function(){
							enabledBlockControle();
						}
					)

					$( '[name="ht_payjpforkintone_setting_data[kintone-enabled]"]' ).change(
						function(){
							enabledBlockControle();
						}
					)

				}
			);

			$( '[name="ht_payjpforkintone_setting_data[subscription-enabled]"]' ).prop( "disabled", true );
			$( '[name="ht_payjpforkintone_setting_data[payjp-plan-id]"]' ).prop( "disabled", true );

			$( '[name="ht_payjpforkintone_setting_data[kintone-fieldcode-for-payjp-subscription-plan-id]"]' ).prop( "disabled", true );
			$( '[name="ht_payjpforkintone_setting_data[kintone-fieldcode-for-payjp-subscription-amount]"]' ).prop( "disabled", true );
			$( '[name="ht_payjpforkintone_setting_data[kintone-fieldcode-for-payjp-customer-id]"]' ).prop( "disabled", true );
			$( '[name="ht_payjpforkintone_setting_data[kintone-fieldcode-for-payjp-subscription-id]"]' ).prop( "disabled", true );

		}

		$(
			function(){
				main();
			}
		);

	}
)( jQuery );

