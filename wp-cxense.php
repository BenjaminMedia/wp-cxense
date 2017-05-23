<?php
/**
 * Plugin Name: WP cXense
 * Version: 1.0.6
 * Plugin URI: https://github.com/BenjaminMedia/wp-cxense
 * Description: This plugin integrates your site with cXense by adding meta tags and calling the cXense api
 * Author: Bonnier - Alf Henderson
 * License: GPL v3
 */

namespace Bonnier\WP\Cxense;

use Bonnier\WP\Cxense\Assets\Scripts;
use Bonnier\WP\Cxense\Models\Post;
use Bonnier\WP\Cxense\Http\HttpRequest;
use Bonnier\WP\Cxense\Services\CxenseApi;
use Bonnier\WP\Cxense\Services\DocumentSearch;
use Bonnier\WP\Cxense\Settings\SettingsPage;
use Bonnier\WP\Cxense\Widgets\Widget;

// Do not access this file directly
if (!defined('ABSPATH')) {
    exit;
}

// Handle autoload so we can use namespaces
spl_autoload_register(function ($className) {
    if (strpos($className, __NAMESPACE__) !== false) {
        $className = str_replace("\\", DIRECTORY_SEPARATOR, $className);
        require_once(__DIR__ . DIRECTORY_SEPARATOR . Plugin::CLASS_DIR . DIRECTORY_SEPARATOR . $className . '.php');
    }
});

// Load plugin api
require_once (__DIR__ . '/'.Plugin::CLASS_DIR.'/api.php');

class Plugin
{
    /**
     * Text domain for translators
     */
    const TEXT_DOMAIN = 'wp-cxense';

    const CLASS_DIR = 'src';

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
    public $plugin_dir;

    /**
     * @var string Plugins url for this plugin.
     */
    public $plugin_url;

    /**
     * Do not load this more than once.
     */
    private function __construct()
    {
        // Set plugin file variables
        $this->file = __FILE__;
        $this->basename = plugin_basename($this->file);
        $this->plugin_dir = plugin_dir_path($this->file);
        $this->plugin_url = plugin_dir_url($this->file);

        // Load textdomain
        load_plugin_textdomain(self::TEXT_DOMAIN, false, dirname($this->basename) . '/languages');

        $this->settings = new SettingsPage();
    }

    private function bootstrap() {

        Post::watch_post_changes($this->settings);
        Scripts::bootstrap($this->settings);
        CxenseApi::bootstrap($this->settings);
    }

    /**
     * Returns the instance of this class.
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self;
            global $wp_cxense;
            $wp_cxense = self::$instance;
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
    public function render_widget() {

        return Widget::render($this->settings);

    }

    /**
     * Returns an array of the items to be displayed in the widget for the current page or null on failure
     *
     * @return null|array
     */
    public function get_widget_data() {

        return Widget::get_widget_data($this->settings);

    }
	
	/**
	 * Search documents
	 *
	 * @param array $arrSearch
	 * @return array
	 */
	public function search_documents(array $arrSearch) {
		
		return DocumentSearch::get_instance($arrSearch)->set_settings($this->settings)->get_results();

	}
	
	/**
	 * Get facets
	 *
	 * @param array $arrSearch
	 * @return array
	 */
	public function get_facets(array $arrSearch) {
		
		return DocumentSearch::get_instance($arrSearch)->set_settings($this->settings)->get_facets();

	}
}

/**
 * @return Plugin $instance returns an instance of the plugin
 */
function instance()
{
    return Plugin::instance();
}

add_action('plugins_loaded', __NAMESPACE__ . '\instance', 0);
