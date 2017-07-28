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
		add_settings_section( 'simpletts_settings_section', false, '__return_false', 'simpletts' );
		$options = array(
			'simpletts_access_key' => array(
				'label'        => __( 'Access Key', 'simpletts' ),
				'render_cb'    => 'render_text_input_field',
			),
			'simpletts_secret_key' => array(
				'label'        => __( 'Secret Key', 'simpletts' ),
				'render_cb'    => 'render_text_input_field',
			),
			'simpletts_aws_region' => array(
				'label'        => __( 'AWS Region', 'simpletts' ),
				'render_cb'    => 'render_aws_region_select_field',
			),
			'simpletts_default_voice' => array(
				'label'        => __( 'Default Voice', 'simpletts' ),
				'render_cb'    => 'render_voice_select_field',
			),
		);
		foreach ( $options as $option => $args ) {
			register_setting( 'simpletts', $option );
			$args['option'] = $option;
			add_settings_field( $option, $args['label'], array( __CLASS__, $args['render_cb'] ), 'simpletts', 'simpletts_settings_section', $args );
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
	public static function render_aws_region_select_field( $args ) {
		$value = self::get_option( $args['option'] );
		$options = array(
			'us-east-2'    => 'US East (Ohio)',
			'us-east-1'    => 'US East (N. Virginia)',
			'us-west-2'    => 'US West (Oregon)',
			'eu-west-1'    => 'EU (Ireland)',
		);
		?>
		<select name="<?php echo esc_attr( $args['option'] ); ?>">
			<?php foreach ( $options as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $value ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Render a voice select field for the settings page.
	 *
	 * @param array $args Arguments to use rendering the field.
	 */
	public static function render_voice_select_field( $args ) {
		$value = self::get_option( $args['option'] );
		$opt_groups = array();
		$voices = Converter::get_available_voices();
		foreach ( $voices as $voice ) {
			if ( ! isset( $opt_groups[ $voice['lang'] ] ) ) {
				$opt_groups[ $voice['lang'] ] = array();
			}
			$opt_groups[ $voice['lang'] ][] = $voice['name'];
		}
		$name = isset( $args['name'] ) ? $args['name'] : $args['option'];
		?>
		<select name="<?php echo esc_attr( $name ); ?>">
			<?php foreach ( $opt_groups as $lang => $options ) : ?>
				<optgroup label="<?php echo esc_attr( $lang ); ?>">
				<?php foreach ( $options as $voice ) : ?>
					<option value="<?php echo esc_attr( $voice ); ?>" <?php selected( $voice, $value ); ?>><?php echo esc_html( $voice ); ?></option>
				<?php endforeach; ?>
				</optgroup>
			<?php endforeach; ?>
		</select>
		<p class="description"><?php esc_html_e( 'Choose from one of Amazon Polly\'s available voices.', 'simpletts' ); ?></p>
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
			case 'simpletts_default_voice':
				$default = 'Joanna';
				break;
			default:
				$default = '';
				break;
		}
		return get_option( $option_name, $default );
	}

}
