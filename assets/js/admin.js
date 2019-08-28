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
		}

		function main(){

			$(
				function(){
					$( '[name="payjpforkintone_setting_data[payjpforkintone-enabled]"]' )
					.checkboxradio();
					$( '[name="payjpforkintone_setting_data[live-enabled]"]' )
					.checkboxradio();
					$( '.chosen-select' )
					.chosen( { allow_single_deselect: true } );

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
				}
			);
		}

		main();

	}
)( jQuery );

