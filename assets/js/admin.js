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

		}

		function main(){
			$(
				function(){
					$( '[name="ht_payjpforkintone_setting_data[payjpforkintone-enabled]"]' ).checkboxradio();
					$( '[name="ht_payjpforkintone_setting_data[payment-type]"]' ).checkboxradio();
					$( '[name="ht_payjpforkintone_setting_data[live-enabled]"]' ).checkboxradio();
					$( '[name="ht_payjpforkintone_setting_data[kintone-enabled]"]' ).checkboxradio();

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

			$( '[name="ht_payjpforkintone_setting_data[payjp-plan-id]"]' ).prop( "disabled", true );
			$( '[name="ht_payjpforkintone_setting_data[payment-type]"]:eq(1)' ).prop( "disabled", true );
			$( "#payjp-fixed-subscription-month" ).prop( "disabled", true );
			$( "#payjp-fixed-subscription-time" ).prop( "disabled", true );

		}

		$(
			function(){
				main();
			}
		);

	}
)( jQuery );
