<?php

namespace Bonnier\WP\Cxense\Models;

use Bonnier\WP\Cxense\Services\CxenseApi;
use Bonnier\WP\Cxense\Settings\SettingsPage;

class Post
{
    private static $settings;

    public static function watch_post_changes(SettingsPage $settingsPage)
    {
        self::$settings = $settingsPage;

        // Ping crawler when post is changed
        add_action('save_post', [__CLASS__, 'update_post']);
        add_action('delete_post', [__CLASS__, 'delete_post']);
    }

    public static function update_post($postId)
    {

        // We have to set the current locale on the settings page in order to get the correct localized settings
        // for the current context.
        self::set_current_lang_from_post_id($postId);

        if (self::$settings->get_enabled()) {
            return CxenseApi::pingCrawler($postId);
        }
    }

    public static function delete_post($postId)
    {
        return CxenseApi::pingCrawler($postId, true);
    }

    public static function is_published($postId)
    {
        return get_post_status($postId) === 'publish';
    }

    /**
     * Sets the locale on the settings page based upon to the locale of the post
     *
     * @param $postId
     */
    private static function set_current_lang_from_post_id($postId)
    {
        if (self::$settings->languages_is_enabled()) {
            self::$settings->set_current_locale(pll_get_post_language($postId));
        }
    }

}
