<?php
/**
 * Enqueuing of scripts and styles
 *
 * @package Simpletts
 */

namespace Simpletts;

/**
 * Enqueuing of scripts and styles
 */
class Assets {

	/**
	 * Enqueue scripts and styles for the editor
	 */
	public static function action_wp_enqueue_editor() {
		$time = filemtime( dirname( dirname( __FILE__ ) ) . '/assets/js/editor.js' );
		wp_enqueue_script( 'simpletts-editor', plugins_url( 'assets/js/editor.js?r=' . (int) $time, dirname( __FILE__ ) ), array( 'jquery', 'mce-view' ) );
		$time = filemtime( dirname( dirname( __FILE__ ) ) . '/assets/css/editor.css' );
		wp_enqueue_style( 'simpletts-editor', plugins_url( 'assets/css/editor.css?r=' . (int) $time, dirname( __FILE__ ) ) );
		if ( ! did_action( 'admin_footer' ) && ! doing_action( 'admin_footer' ) ) {
			add_action( 'admin_footer', array( __CLASS__, 'action_admin_footer_render_template' ) );
		} else {
			self::action_admin_footer_render_template();
		}
	}

	/**
	 * Render the editor template in the footer.
	 */
	public static function action_admin_footer_render_template() {
		static $rendered_once;
		if ( isset( $rendered_once ) ) {
			return;
		}
		$rendered_once = true;
		echo self::get_template_part( 'editor' );
	}

	/**
	 * Get a rendered template.
	 *
	 * @param string $template Template to render.
	 * @param array  $vars     Any variables to include in the template.
	 * @return string
	 */
	private static function get_template_part( $template, $vars = array() ) {
		$full_path = dirname( __DIR__ ) . '/parts/' . $template . '.php';
		if ( ! file_exists( $full_path ) ) {
			return '';
		}

		ob_start();
		// @codingStandardsIgnoreStart
		if ( ! empty( $vars ) ) {
			extract( $vars );
		}
		// @codingStandardsIgnoreEnd
		include $full_path;
		return ob_get_clean();
	}

}
