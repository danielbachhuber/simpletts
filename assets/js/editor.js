(function($){

	var Editor = {
		initialize: function() {
			this.container = $('.simpletts-modal-container');
			this.bindEvents();
		},

		bindEvents: function() {
			$('button.simpletts-convert-text').on('click', $.proxy( function( event ){
				var elem = $( event.currentTarget ),
					editorId = elem.data('editor');

				event.preventDefault();
				// Prevents Opera from showing the outline of the button above the modal.
				//
				// See: https://core.trac.wordpress.org/ticket/22445
				elem.blur();

				this.open();
			}, this ) );
		},

		/**
		 * Open the modal experience to convert text
		 */
		open: function( data = null ) {
			var template = wp.template( 'simpletts-convert-text' );
			$( '.simpletts-frame-content', this.container ).html( template( null !== data ? data : {} ) );
			this.container.addClass('simpletts-state-creating');
			$('body').addClass('simpletts-modal-open');
			$(document).on('keydown.simpletts-escape', $.proxy( function( event ){
				if ( 27 === event.keyCode ) {
					this.close( false );
					event.stopImmediatePropagation();
				}
			}, this ));
			$('form', this.container).on('submit', $.proxy(function( event ){
				event.preventDefault();
				this.close( true );
			}, this ));
			this.container.show();
		},

		close: function( convert ) {
			$('body').removeClass('simpletts-modal-open');
			$(document).unbind('keydown.simpletts-escape');
			var hideContainer = $.proxy( function(){
				this.container.hide();
				this.container.removeClass('simpletts-state-creating simpletts-state-editing');
				$( '.simpletts-frame-content', this.container ).empty();
			}, this );
		}
	}

	$(document).ready(function(){
		Editor.initialize();
	});

}(jQuery))
