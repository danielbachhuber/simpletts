(function($, wp){

	var Editor = {

		action: 'simpletts_convert_text',
		editor_id: null,

		initialize: function() {
			this.container = $('.simpletts-modal-container');
			this.bindEvents();
		},

		bindEvents: function() {
			$('button.simpletts-convert-text').on('click', $.proxy( function( event ){
				var elem = $( event.currentTarget ),
					editorId = elem.data('editor');

				this.editor_id = editorId;

				event.preventDefault();
				// Prevents Opera from showing the outline of the button above the modal.
				//
				// See: https://core.trac.wordpress.org/ticket/22445
				elem.blur();

				this.open();
			}, this ) );
			$('.simpletts-button-insert', this.container).on('click', $.proxy( function( event ){
				this.close( true );
			}, this ));
			$('.simpletts-modal-close', this.container).on('click', $.proxy( function( event ){
				this.close( false );
			}, this ));
		},

		/**
		 * Open the modal experience to convert text
		 */
		open: function( data = null ) {
			this.renderTemplate( data );
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

		renderTemplate( data = null ) {
			var template = wp.template( 'simpletts-convert-text' );
			$( '.simpletts-frame-content', this.container ).html( template( null !== data ? data : {} ) );
		},

		close: function( convert ) {
			$('body').removeClass('simpletts-modal-open');
			$(document).unbind('keydown.simpletts-escape');
			var hideContainer = $.proxy( function(){
				this.container.hide();
				this.container.removeClass('simpletts-state-creating simpletts-state-editing');
				$( '.simpletts-frame-content', this.container ).empty();
			}, this );

			if ( ! convert ) {
				hideContainer();
				return;
			}

			var formData = {};
			$.each( $('form', this.container ).serializeArray(), function(_, kv) {
				if (formData.hasOwnProperty(kv.name)) {
					formData[kv.name] = $.makeArray(formData[kv.name]);
					formData[kv.name].push(kv.value);
				} else {
					formData[kv.name] = kv.value;
				}
			});

			wp.ajax.post( this.action, formData )
			.done( $.proxy( function( response ) {
				if ( wp && wp.media && wp.media.editor ) {
					var id = wp.media.editor.id( this.editor_id );
					wp.media.editor.activeEditor = this.editor_id;
					var workflow = wp.media.editor.get( this.editor_id );

					// Redo workflow if state has changed
					if ( ! workflow || ( workflow.options && options.state !== workflow.options.state ) ) {
						workflow = wp.media.editor.add( this.editor_id );
					}

					wp.media.frame = workflow;
					wp.media.frame.on( 'open', function(){
						var selection = wp.media.frame.state().get('selection');
						if ( response.attachment_id ) {
							selection.add( wp.media.attachment( response.attachment_id ) );
						}
					});
					wp.media.frame.open();
				}
				hideContainer();
			}, this ) )
			.fail( $.proxy( function( response ) {
				formData.errorMessage = response.message;
				this.renderTemplate( formData );
			}, this ) );

		}
	}

	$(document).ready(function(){
		Editor.initialize();
	});

}(jQuery, window.wp))
