<?php

/**
 * Returns an instance of the wp_cxense plugin
 *
 * @return \Bonnier\WP\Cxense\Plugin|null
 */
function wp_cxense()
{
    return isset($GLOBALS['wp_cxense']) ? $GLOBALS['wp_cxense'] : null;
}