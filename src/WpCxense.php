<?php

namespace Bonnier\WP\Cxense;

use Bonnier\WP\Cxense\Assets\Scripts;
use Bonnier\WP\Cxense\Models\Post;
use Bonnier\WP\Cxense\Services\CxenseApi;
use Bonnier\WP\Cxense\Services\DocumentSearch;
use Bonnier\WP\Cxense\Services\WidgetDocument;
use Bonnier\WP\Cxense\Settings\SettingsPage;
use Bonnier\WP\Cxense\Widgets\Widget;

class WpCxense
{
    /**
     * Text domain for translators
     */
    const TEXT_DOMAIN = 'wp-cxense';

    /**
     * @var object Instance of this class.
     */
    private static $instance;

    /**
     * @var object Instance of the settings page.
     */
    public $settings;

    /**
     * @var string Filename of this class.
     */
    public $file;

    /**
     * @var string Basename of this class.
     */
    public $basename;

    /**
     * @var string Plugins directory for this plugin.
     */
    public $pluginDir;

    /**
     * @var Object
     */
    public $scripts;

    /**
     * @var string Plugins url for this plugin.
     */
    public $pluginUrl;

    /**
     * Do not load this more than once.
     */
    private function __construct()
    {
        // Set plugin file variables
        $this->file = __FILE__;
        $this->basename = plugin_basename($this->file);
        $this->pluginDir = plugin_dir_path($this->file);
        $this->pluginUrl = plugin_dir_url($this->file);

        // Load textdomain
        load_plugin_textdomain(self::TEXT_DOMAIN, false, dirname($this->basename) . '/languages');

        $this->settings = new SettingsPage();
        $this->scripts = new Scripts();

        Post::watch_post_changes();
        $this->scripts->bootstrap();
    }

    /**
     * Returns the instance of this class.
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self;

            /**
             * Run after the plugin has been loaded.
             */
            do_action('wp_cxense_loaded');
        }

        return self::$instance;
    }

    /**
     * Search documents
     *
     * @param array $arrSearch
     * @return \stdClass
     */
    public function search_documents(array $arrSearch)
    {
        return DocumentSearch::get_instance()
            ->set_search($arrSearch)
            ->set_settings()
            ->get_documents();
    }
}
