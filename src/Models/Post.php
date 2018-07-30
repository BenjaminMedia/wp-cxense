<?php

namespace Bonnier\WP\Cxense\Models;

use Bonnier\Willow\MuPlugins\LanguageProvider;
use Bonnier\WP\Cxense\Services\CxenseApi;
use Bonnier\WP\Cxense\Settings\SettingsPage;

class Post
{
    private static $settings;

    public static function watch_post_changes(SettingsPage $settingsPage)
    {
        self::$settings = $settingsPage;

        // Ping crawler when post is changed
        add_action('transition_post_status', [__CLASS__, 'post_status_changed'], 10, 3);
    }

    public static function post_status_changed($new_status, $old_status, $post)
    {
        if ($old_status === 'draft' && $new_status === 'trash') {
            return;
        }

        if ($new_status === 'publish') {
            self::update_post($post->ID);
        } elseif ($new_status === 'trash') {
            self::delete_post($post->ID);
        }
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
            self::$settings->set_current_locale(LanguageProvider::getPostLanguage($postId));
        }
    }
}
