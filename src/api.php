<?php

/**
 * Returns an instance of the bp-wa-oauth plugin
 *
 * @return \Bonnier\WP\WaOauth\Plugin|null
 */
function wp_cxense()
{
    return isset($GLOBALS['wp_cxense']) ? $GLOBALS['wp_cxense'] : null;
}