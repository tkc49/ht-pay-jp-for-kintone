( function( $ ){

	function enabledBlockControle(){
		const subscriptionFlag = $( '[name="ht_payjpforkintone_setting_data[subscription-enabled]"]:checked' ).val();
		if ( 'enable' === subscriptionFlag ) {
			$( '[name="ht_payjpforkintone_setting_data[amount-cf7-mailtag]"]' ).prop( "disabled", true ).trigger( "chosen:updated" );
			$( '[name="ht_payjpforkintone_setting_data[kintone-fieldcode-for-payjp-billing-id]"]' ).prop( "disabled", true );

			$( '[name="ht_payjpforkintone_setting_data[payjp-plan-id]"]' ).prop( "disabled", false );

			const kintoneEnabled = $( '[name="ht_payjpforkintone_setting_data[kintone-enabled]"]:checked' ).val();
			if ( kintoneEnabled === 'enable' ) {

				$( '[name="ht_payjpforkintone_setting_data[kintone-fieldcode-for-payjp-subscription-plan-id]"]' ).prop( "disabled", false );
				$( '[name="ht_payjpforkintone_setting_data[kintone-fieldcode-for-payjp-subscription-amount]"]' ).prop( "disabled", false );
				$( '[name="ht_payjpforkintone_setting_data[kintone-fieldcode-for-payjp-customer-id]"]' ).prop( "disabled", false );
				$( '[name="ht_payjpforkintone_setting_data[kintone-fieldcode-for-payjp-subscription-id]"]' ).prop( "disabled", false );

			} else {
				$( '[name="ht_payjpforkintone_setting_data[kintone-fieldcode-for-payjp-subscription-plan-id]"]' ).prop( "disabled", true );
				$( '[name="ht_payjpforkintone_setting_data[kintone-fieldcode-for-payjp-subscription-amount]"]' ).prop( "disabled", true );
				$( '[name="ht_payjpforkintone_setting_data[kintone-fieldcode-for-payjp-customer-id]"]' ).prop( "disabled", true );
				$( '[name="ht_payjpforkintone_setting_data[kintone-fieldcode-for-payjp-subscription-id]"]' ).prop( "disabled", true );
			}

		} else {

			const kintoneEnabled = $( '[name="ht_payjpforkintone_setting_data[kintone-enabled]"]:checked' ).val();
			if ( kintoneEnabled === 'enable' ) {
				$( '[name="ht_payjpforkintone_setting_data[kintone-fieldcode-for-payjp-billing-id]"]' ).prop( "disabled", false );
			}

			$( '[name="ht_payjpforkintone_setting_data[amount-cf7-mailtag]"]' ).prop( "disabled", false ).trigger( "chosen:updated" );
			$( '[name="ht_payjpforkintone_setting_data[payjp-plan-id]"]' ).prop( "disabled", true );

			$( '[name="ht_payjpforkintone_setting_data[kintone-fieldcode-for-payjp-subscription-plan-id]"]' ).prop( "disabled", true );
			$( '[name="ht_payjpforkintone_setting_data[kintone-fieldcode-for-payjp-subscription-amount]"]' ).prop( "disabled", true );
			$( '[name="ht_payjpforkintone_setting_data[kintone-fieldcode-for-payjp-customer-id]"]' ).prop( "disabled", true );
			$( '[name="ht_payjpforkintone_setting_data[kintone-fieldcode-for-payjp-subscription-id]"]' ).prop( "disabled", true );

		}

	}

	function main(){

		$(
			function(){
				$( '[name="ht_payjpforkintone_setting_data[subscription-enabled]"]' ).change(
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

		$( '[name="ht_payjpforkintone_setting_data[subscription-enabled]"]' ).prop( "disabled", false );
		$( '[name="ht_payjpforkintone_setting_data[payjp-plan-id]"]' ).prop( "disabled", false );

		$( '[name="ht_payjpforkintone_setting_data[kintone-fieldcode-for-payjp-subscription-plan-id]"]' ).prop( "disabled", false );
		$( '[name="ht_payjpforkintone_setting_data[kintone-fieldcode-for-payjp-subscription-amount]"]' ).prop( "disabled", false );
		$( '[name="ht_payjpforkintone_setting_data[kintone-fieldcode-for-payjp-customer-id]"]' ).prop( "disabled", false );
		$( '[name="ht_payjpforkintone_setting_data[kintone-fieldcode-for-payjp-subscription-id]"]' ).prop( "disabled", false );

		enabledBlockControle();

		// Free版のほうのJSが後に動いてしまい、payjp-billing-id の disabled が外れてしまうので、MutationObserverで監視
		const target   = document.getElementById( 'payjp-billing-id' );
		const observer = new MutationObserver(
			records => {
				observer.disconnect();
				enabledBlockControle();
			}
		);
		observer.observe(
			target,
			{
				attributes: true
			}
		);

	}

	$(
		function(){
			main();
		}
	);

} )( jQuery );
