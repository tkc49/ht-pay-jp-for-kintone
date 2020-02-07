( function( $ ){

	function enabledBlockControle(){
		const paymentType = $( '[name="ht_payjpforkintone_setting_data[payment-type]"]:checked' ).val();
		if ( 'subscription' === paymentType ) {
			$( '[name="ht_payjpforkintone_setting_data[amount-cf7-mailtag]"]' ).prop( "disabled", true ).trigger( "chosen:updated" );

			$( '[name="ht_payjpforkintone_setting_data[payjp-plan-id]"]' ).prop( "readonly", false );


		} else {

			$( '[name="ht_payjpforkintone_setting_data[amount-cf7-mailtag]"]' ).prop( "disabled", false ).trigger( "chosen:updated" );
			$( '[name="ht_payjpforkintone_setting_data[payjp-plan-id]"]' ).prop( "readonly", true );

		}

	}

	function main(){

		$(
			function(){
				$( '[name="ht_payjpforkintone_setting_data[payment-type]"]' ).change(
					function(){

						enabledBlockControle();
					}
				)
			}
		);
		$(
			function(){
				$( '[name="ht_payjpforkintone_setting_data[kintone-enabled]"]' ).change(
					function(){

						enabledBlockControle();
					}
				)
			}
		);

		$( '[name="ht_payjpforkintone_setting_data[payment-type]"]:eq(1)' ).prop( "disabled", false );
		$( '[name="ht_payjpforkintone_setting_data[payjp-plan-id]"]' ).prop( "disabled", false );
		$( "#payjp-fixed-subscription-month" ).prop( "disabled", false );
		$( "#payjp-fixed-subscription-time" ).prop( "disabled", false );

		enabledBlockControle();

	}

	$(
		function(){
			main();
		}
	);

} )( jQuery );
