<?php

namespace Bonnier\WP\Cxense\Models;

use Bonnier\WP\Cxense\Services\CxenseApi;
use Bonnier\WP\Cxense\Settings\SettingsPage;

class Post
{
    public static function watch_post_changes(SettingsPage $settingsPage) {

        if( $settingsPage->get_enabled() ) {

            // Ping crawler when post is changed
            add_action('save_post', [__CLASS__, 'update_post']);
        }

        add_action('delete_post', [__CLASS__, 'delete_post']);
    }

    public static function update_post($postId) {

        return CxenseApi::pingCrawler($postId);
    }

    public static function delete_post($postId) {

        return CxenseApi::pingCrawler($postId, true);
    }

    public static function is_published($postId) {
        return get_post_status($postId) === 'publish';
    }
}
