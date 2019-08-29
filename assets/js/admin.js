// 初期設定.
(
	function( $ ){

		function enabledBlockControle(){

			const enabled = $( '[name="payjpforkintone_setting_data[payjpforkintone-enabled]"]:checked' )
			.val();

			if ( enabled === 'enable' ) {
				$( '#js-payjpforkintone-enabled-block' )
				.show();
			} else {
				$( '#js-payjpforkintone-enabled-block' )
				.hide();
			}

			const kintoneEnabled = $( '[name="payjpforkintone_setting_data[kintone-enabled]"]:checked' )
			.val();

			if ( kintoneEnabled === 'enable' ) {
				console.log( '活性' );
				$( '[name="payjpforkintone_setting_data[kintone-fieldcode-for-payjp-billing-id]"]' )
				.prop( 'disabled', false )
				.trigger( "chosen:updated" );
				;
			} else {
				console.log( '非活性' );
				$( '[name="payjpforkintone_setting_data[kintone-fieldcode-for-payjp-billing-id]"]' )
				.prop( 'disabled', true )
				.trigger( "chosen:updated" );
				;

			}

		}

		function main(){

			$(
				function(){
					$( '[name="payjpforkintone_setting_data[payjpforkintone-enabled]"]' )
					.checkboxradio();
					$( '[name="payjpforkintone_setting_data[live-enabled]"]' )
					.checkboxradio();
					$( '[name="payjpforkintone_setting_data[kintone-enabled]"]' )
					.checkboxradio();


					$( '.chosen-select' )
					.chosen(
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
					$( '[name="payjpforkintone_setting_data[payjpforkintone-enabled]"]' )
					.change(
						function(){
							enabledBlockControle();
						}
					)

					$( '[name="payjpforkintone_setting_data[kintone-enabled]"]' )
					.change(
						function(){
							enabledBlockControle();
						}
					)

				}
			);
		}

		main();

	}
)( jQuery );

