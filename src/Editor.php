<?php
/**
 * Integration points with the WordPress editor
 *
 * @package Simpletts
 */

namespace Simpletts;

use WP_Error;

/**
 * Integration points with the WordPress editor
 */
class Editor {

	/**
	 * Render the 'Convert Text to Speech' media button
	 *
	 * @param string $editor_id TinyMCE editor ID.
	 */
	public static function action_media_buttons( $editor_id ) {
		?>
		<button type="button" class="button simpletts-convert-text" data-editor="<?php echo esc_attr( $editor_id ); ?>">
			<span class="wp-media-buttons-icon dashicons dashicons-controls-volumeon"></span>
			<?php esc_html_e( 'Convert Text to Speech', 'simplettts' ); ?>
		</button>
		<?php
	}

}
