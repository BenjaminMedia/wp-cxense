<?php
/**
 * Plugin Name: WP cXense
 * Version: 3.2.9
 * Plugin URI: https://github.com/BenjaminMedia/wp-cxense
 * Description: This plugin integrates your site with cXense by adding meta tags and calling the cXense api
 * Author: Bonnier - Alf Henderson
 * License: GPL v3
 */

/**
 * @return \Bonnier\WP\Cxense\WpCxense $instance returns an instance of the plugin
 */
function registerWpCxense()
{
    return Bonnier\WP\Cxense\WpCxense::instance();
}

add_action('plugins_loaded', 'registerWpCxense');
