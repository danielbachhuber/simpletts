<?php
/**
 * Renders modal UX for the WordPress editor.
 *
 * @package Simpletts
 */

?>

<script type="text/html" id="tmpl-simpletts-convert-text">
	<# if ( data.errorMessage ) { #>
		<div class="error"><p>{{ data.errorMessage }}</p></div>
	<# } #>
	<form>
		<fieldset>
			<label for="text"><?php esc_html_e( 'Text to convert', 'simpletts' ); ?></label>
			<textarea name="text" required>{{ data.text }}</textarea>
		</fieldset>
		<fieldset>
			<label for="voice"><?php esc_html_e( 'Voice to use', 'simpletts' ); ?></label>
			<?php
			Simpletts\Settings::render_voice_select_field( array(
				'option' => 'simpletts_default_voice',
				'name'   => 'voice',
			) );
			?>
		<input type="hidden" name="post_id" value="<?php echo (int) get_the_ID(); ?>" />
		<?php wp_nonce_field( 'simpletts', 'nonce' ); ?>
	</form>
</script>

<div class="simpletts-modal-container" style="display:none">
	<div class="simpletts-modal wp-core-ui">
		<button type="button" class="simpletts-modal-close"><span class="simpletts-modal-icon"><span class="screen-reader-text"><?php esc_html_e( 'Close Modal', 'simpletts' ); ?></span></span></button>

		<div class="simpletts-modal-content">
			<div class="simpletts-frame wp-core-ui">
				<div class="simpletts-frame-title">
					<h1 class="simpletts-state-creating"><?php echo esc_html( 'Convert Text to Speech' ); ?></h1>
				</div>
				<div class="simpletts-frame-content" tabindex="-1"></div>

				<div class="simpletts-frame-toolbar">
					<div class="simpletts-toolbar">
						<div class="simpletts-toolbar-primary search-form">
							<button type="button" class="button simpletts-button simpletts-state-creating button-primary button-large simpletts-button-insert"><?php esc_html_e( 'Convert Text to Speech', 'simpletts' ); ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="simpletts-modal-backdrop"></div>

</div>
