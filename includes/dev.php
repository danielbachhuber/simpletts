<?php
/**
 * Loaded for development versions of the plugin
 *
 * @package Simpletts
 */

add_action( 'wp_head', function() {
	echo '<!-- This site is running a development version of Simple Text-to-Speech ' . SIMPLETTS_VERSION . ' - https://simpletts.io -->' . PHP_EOL;
});
