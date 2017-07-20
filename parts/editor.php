<script type="text/html" id="tmpl-simpletts-convert-text">
	<form>
		<fieldset>
			<label for="text"><?php esc_html_e( 'Text to convert', 'simpletts' ); ?></label>
			<textarea name="text">{{ data.text }}</textarea>
		</fieldset>
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
				<div class="simpletts-frame-content" tabindex="-1">
					<?php /** Set dynamically with JS **/ ?>
				</div>

				<div class="simpletts-frame-toolbar">
					<div class="simpletts-toolbar">
						<div class="simpletts-toolbar-primary search-form">
							<button type="button" class="button simpletts-button simpletts-state-creating button-primary button-large simpletts-button-convert"><?php esc_html_e( 'Convert Text to Speech', 'simpletts' ); ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="simpletts-modal-backdrop"></div>

</div>
