jQuery( document ).ready( function ( $ ) {

	var vatNumberVar = {

		_self: null,

		init: function() {
			this._self = $( '#vat_number' );
	
			if ( ! this._self.length ) {
				return !1;
			}
	
			$( '#billing_company' ).on( 'input', this.visibleCallback );
		},

		visibleCallback: function( event ) {

			if ( this.value === '' ) {
				vatNumberVar._self.closest( '.form-row' ).addClass( 'hidden' );
			} else {
				vatNumberVar._self.closest( '.form-row' ).removeClass( 'hidden' );
			}
		}
	};

	vatNumberVar.init();
} );
