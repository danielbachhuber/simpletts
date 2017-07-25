<?php
/**
 * Handles settings page configuration.
 *
 * @package Simpletts
 */

namespace Simpletts;

/**
 *  Handles settings page configuration.
 */
class Settings {

	/**
	 * Register settings for the plugin.
	 */
	public static function action_admin_init() {
		add_settings_section( 'simpletts_settings_section', __( 'Configuration Settings', 'simpletts' ), '__return_false', 'simpletts' );
		$options = array(
			'simpletts_access_key' => __( 'Access Key', 'simpletts' ),
			'simpletts_secret_key' => __( 'Secret Key', 'simpletts' ),
			'simpletts_aws_region' => __( 'AWS Region', 'simpletts' ),
		);
		foreach ( $options as $option => $label ) {
			if ( in_array( $option, array( 'simpletts_aws_region' ), true ) ) {
				$render_cb = 'render_select_field';
			} else {
				$render_cb = 'render_text_input_field';
			}
			register_setting( 'simpletts', $option );
			add_settings_field( $option, $label, array( __CLASS__, $render_cb ), 'simpletts', 'simpletts_settings_section', array(
				'option'   => $option,
			) );
		}
	}

	/**
	 * Register the menu page for the plugin.
	 */
	public static function action_admin_menu() {
		add_options_page( __( 'Simple Text-to-Speech', 'simpletts' ), __( 'Simple Text-to-Speech', 'simpletts' ), 'manage_options', 'simpletts', array( __CLASS__, 'render_settings_page' ) );
	}

	/**
	 * Renders the settings page.
	 */
	public static function render_settings_page() {
		?>
		<form action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>" method="post">

			<h2><?php esc_html_e( 'Simple Text-to-Speech', 'simpletts' ); ?></h2>

			<?php
			settings_fields( 'simpletts' );
			do_settings_sections( 'simpletts' );
			submit_button();
			?>

		</form>
		<?php
	}

	/**
	 * Render a text input for the settings page.
	 *
	 * @param array $args Arguments to use rendering the field.
	 */
	public static function render_text_input_field( $args ) {
		$value = self::get_option( $args['option'] );
		?>
		<input type="text" name="<?php echo esc_attr( $args['option'] ); ?>" class="regular-text" value="<?php echo esc_attr( $value ); ?>">
		<?php
	}

	/**
	 * Render a select field for the settings page.
	 *
	 * @param array $args Arguments to use rendering the field.
	 */
	public static function render_select_field( $args ) {
		$value = self::get_option( $args['option'] );
		switch ( $args['option'] ) {
			case 'simpletts_aws_region':
				$options = array(
					'us-east-2'    => 'US East (Ohio)',
					'us-east-1'    => 'US East (N. Virginia)',
					'us-west-2'    => 'US West (Oregon)',
					'eu-west-1'    => 'EU (Ireland)',
				);
				break;
			default:
				$options = array();
				break;
		}
		?>
		<select name="<?php echo esc_attr( $args['option'] ); ?>">
			<?php foreach ( $options as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $value ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Get an option, but use a default value if no option is present
	 *
	 * @param string $option_name Name of the option.
	 * @return mixed
	 */
	public static function get_option( $option_name ) {
		switch ( $option_name ) {
			case 'simpletts_aws_region':
				$default = 'us-east-1';
				break;
			default:
				$default = '';
				break;
		}
		return get_option( $option_name, $default );
	}

}
