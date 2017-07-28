<?php
/**
 * Handles admin ajax requests.
 *
 * @package Simpletts
 */

namespace Simpletts;

/**
 *  Handles admin ajax requests.
 */
class Admin_Ajax {

	/**
	 * Handle a request to convert text
	 */
	public static function handle_ajax_convert_text() {

		if ( ! current_user_can( 'edit_posts' )
			|| empty( $_POST['nonce'] )
			|| ! wp_verify_nonce( $_POST['nonce'], 'simpletts' ) ) {
			wp_send_json_error( array(
				'message'     => __( "Sorry, you don't have permission to do this.", 'simpletts' ),
			));
		}

		if ( empty( $_POST['text'] ) ) {
			wp_send_json_error( array(
				'message'     => __( 'Text is required to convert text to speech.', 'simpletts' ),
			));
		}

		$voice = false;
		if ( ! empty( $_POST['voice'] ) ) {
			$voice = sanitize_text_field( $_POST['voice'] );
		}

		$attachment_id = Converter::create_audio_attachment_from_text( $_POST['text'], $voice );
		if ( is_wp_error( $attachment_id ) ) {
			wp_send_json_error( array(
				'message'     => $attachment_id->get_error_message(),
			));
		}
		wp_send_json_success( array(
			'attachment_id'   => (int) $attachment_id,
		) );
	}

}
