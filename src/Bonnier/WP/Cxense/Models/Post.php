<?php

namespace Bonnier\WP\Cxense\Models;

use Bonnier\WP\Cxense\Services\CxenseApi;
use Bonnier\WP\Cxense\Settings\SettingsPage;

class Post
{
    public static function watch_post_changes(SettingsPage $settingsPage) {

        add_action('admin_init', function() use($settingsPage) {

            if( $settingsPage->get_enabled() ) {

                // Ping crawler when post is changed
                add_action('save_post', [__CLASS__, 'ping_cxense_crawler']);
            }

            add_action('delete_post', [__CLASS__, 'ping_cxense_crawler']);

        });
    }

    public static function ping_cxense_crawler($postId) {

        return CxenseApi::pingCrawler($postId);
    }
}