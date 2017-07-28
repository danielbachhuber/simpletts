<?php
/**
 * Plugin Name:     Simple Text-to-Speech
 * Plugin URI:      https://danielbachhuber.com/simpletts/
 * Description:     Create affordable, high-quality audio recordings of your blog posts.
 * Author:          Daniel Bachhuber, Hand Built
 * Author URI:      https://handbuilt.co
 * Text Domain:     simpletts
 * Domain Path:     /languages
 * Version:         0.1.0-alpha
 * License:         GPL v3
 *
 * @package         Simpletts
 */

define( 'SIMPLETTS_VERSION', '0.1.0-alpha' );

/**
 * Simple Text-to-Speech Plugin
 * Copyright (C) 2017, Daniel Bachhuber - daniel@handbuilt.co
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Warn when minimum version requirements aren't met
 */
function simpletts_action_admin_notices_warn_requirements() {
	echo '<div class="error message"><p>' . __( 'Simple Text-to-Speech requires at least WordPress 4.4 and PHP 5.3. Please make sure you meet these minimum requirements.', 'simpletts' ) . '</p></div>';
}

if ( version_compare( $GLOBALS['wp_version'], '4.4', '<' )
	|| version_compare( PHP_VERSION, '5.3', '<' ) ) {
	add_action( 'admin_notices', 'simpletts_action_admin_notices_warn_requirements' );
	return;
}

add_action( 'admin_init', array( 'Simpletts\Settings', 'action_admin_init' ) );
add_action( 'admin_menu', array( 'Simpletts\Settings', 'action_admin_menu' ) );
add_action( 'wp_ajax_simpletts_convert_text', array( 'Simpletts\Admin_Ajax', 'handle_ajax_convert_text' ) );
add_action( 'wp_enqueue_editor', array( 'Simpletts\Assets', 'action_wp_enqueue_editor' ) );
add_action( 'media_buttons', array( 'Simpletts\Editor', 'action_media_buttons' ) );

if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
	require_once dirname( __FILE__ ) . '/vendor/autoload.php';
}

if ( file_exists( dirname( __FILE__ ) . '/includes/dev.php' ) ) {
	require_once dirname( __FILE__ ) . '/includes/dev.php';
}
