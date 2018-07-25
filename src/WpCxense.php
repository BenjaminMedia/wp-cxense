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
    }

    private function bootstrap()
    {
        Post::watch_post_changes($this->settings);
        $this->scripts->bootstrap($this->settings);
        CxenseApi::bootstrap($this->settings);
    }

    /**
     * Returns the instance of this class.
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self;
            self::$instance->bootstrap();

            /**
             * Run after the plugin has been loaded.
             */
            do_action('wp_cxense_loaded');
        }

        return self::$instance;
    }

    /**
     * Render the widget for the current page includes html structure from cXense,
     * returns null on failure and true on success
     *
     * @return bool|null
     *
     */
    public function render_widget()
    {
        return Widget::render($this->settings);
    }

    /**
     * Returns an array of the items to be displayed in the widget for the current page or null on failure
     *
     * @return null|array
     */
    public function get_widget_data()
    {
        return Widget::get_widget_data($this->settings);
    }

    /**
     * Search documents
     *
     * @param array $arrSearch
     * @return array
     */
    public function search_documents(array $arrSearch)
    {
        return DocumentSearch::get_instance()
            ->set_search($arrSearch)
            ->set_settings($this->settings)
            ->get_documents();
    }

    /**
     * Get widget documents
     *
     * @param array $arrInput
     * @return array
     */
    public function get_widget_documents(array $arrInput)
    {

        //If cache is enabled in settings
        if ($this->settings->get_setting_value('enable_query_cache', get_locale())) {
            $strCacheKey = md5(json_encode($arrInput));

            if ($arrResult = wp_cache_get($strCacheKey, 'cxense_plugin')) {
                return $arrResult;
            }

            $arrResult = $this->getResult($arrInput);
            wp_cache_add($strCacheKey, $arrResult, 'cxense_plugin', 30);

            return $arrResult;
        }

        return $this->getResult($arrInput);
    }

    /**
     * @param array $arrInput
     * @return array
     */
    public function getResult(array $arrInput)
    {
        return WidgetDocument::get_instance($arrInput)
            ->set_settings($this->settings)
            ->get_documents();
    }
}
