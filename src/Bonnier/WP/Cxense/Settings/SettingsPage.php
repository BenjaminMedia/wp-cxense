<?php

namespace Bonnier\WP\Cxense\Settings;

use Bonnier\WP\Cxense\Settings\Partials\CustomTaxonomiesSettings;
use Bonnier\WP\Cxense\Settings\Partials\WidgetSettings;
use PLL_Language;

class SettingsPage
{
    const SETTINGS_KEY = 'wp_cxense_settings';
    const SETTINGS_GROUP = 'wp_cxense_settings_group';
    const SETTINGS_SECTION = 'wp_cxense_settings_section';
    const SETTINGS_PAGE = 'wp_cxense_settings_page';
    const Settings_PAGE_NAME = 'cXense';
    const Settings_PAGE_TITLE = 'WP cXense settings:';
    const NOTICE_PREFIX = 'WP cXense:';
    const ROLES_PREFIX = 'bp_wa_';

    private $settingsFields = [
        'site_id' => [
            'type' => 'text',
            'name' => 'Site ID',
        ],
        'api_user' => [
            'type' => 'text',
            'name' => 'Api user',
        ],
        'api_key' => [
            'type' => 'text',
            'name' => 'Api Key',
        ],
        'organisation_prefix' => [
            'type' => 'text',
            'name' => 'Organisation Prefix',
        ],
        'brand' => [
            'type' => 'text',
            'name' => 'Brand name (GDS)',
        ],
        'sortby_widget_id' => [
            'type' => 'text',
            'name' => 'SortBy Widget ID',
        ],
        'persisted_query_id' => [
            'type' => 'text',
            'name' => 'Persisted Query ID',
        ],
        'enable_query_cache' => [
            'type' => 'checkbox',
            'name' => 'Enable Query Cache',
        ],
        'enabled' => [
            'type' => 'checkbox',
            'name' => 'Enabled',
        ],
        WidgetSettings::SETTING_KEY => [
            'type' => 'callback',
            'name' => 'Widgets',
            'callback' => [WidgetSettings::class, 'render'],
            'sanitize_callback' => [WidgetSettings::class, 'sanitize_input']
        ],
        CustomTaxonomiesSettings::SETTING_KEY => [
            'type' => 'callback',
            'name' => 'Searchable Taxonomies',
            'callback' => [CustomTaxonomiesSettings::class, 'render'],
            'sanitize_callback' => [CustomTaxonomiesSettings::class, 'sanitize_input']
        ],
        CustomTaxonomiesSettings::SETTING_KEY_ORDER => [
            'type' => 'callback',
            'name' => 'Taxonomy Order',
            'callback' => [CustomTaxonomiesSettings::class, 'renderTaxonomyOrder'],
            'sanitize_callback' => [CustomTaxonomiesSettings::class, 'sanitize_input']
        ]
    ];

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $settingsValues;

    private $currentLocale;

    /**
     * Start up
     */
    public function __construct()
    {
        $this->settingsValues = get_option(self::SETTINGS_KEY);
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function print_error($error)
    {
        $out = "<div class='error settings-error notice is-dismissible'>";
        $out .= "<strong>" . self::NOTICE_PREFIX . "</strong><p>$error</p>";
        $out .= "</div>";
        print $out;
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin',
            self::Settings_PAGE_NAME,
            'manage_options',
            self::SETTINGS_PAGE,
            array($this, 'create_admin_page')
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property?>
        <div class="wrap">
            <form method="post" action="options.php">
                <?php
                // This prints out all hidden setting fields
                settings_fields(self::SETTINGS_GROUP);
        do_settings_sections(self::SETTINGS_PAGE);
        submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function register_settings()
    {
        if ($this->languages_is_enabled()) {
            $this->enable_language_fields();
        }

        register_setting(
            self::SETTINGS_GROUP, // Option group
            self::SETTINGS_KEY, // Option name
            array($this, 'sanitize') // Sanitize
        );

        add_settings_section(
            self::SETTINGS_SECTION, // ID
            self::Settings_PAGE_TITLE, // Title
            array($this, 'print_section_info'), // Callback
            self::SETTINGS_PAGE // Page
        );

        foreach ($this->settingsFields as $settingsKey => $settingField) {
            add_settings_field(
                $settingsKey, // ID
                $settingField['name'], // Title
                array($this, $settingsKey), // Callback
                self::SETTINGS_PAGE, // Page
                self::SETTINGS_SECTION // Section
            );
        }
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     * @return array
     */
    public function sanitize($input)
    {
        $sanitizedInput = [];

        foreach ($this->settingsFields as $fieldKey => $settingsField) {
            if (isset($input[$fieldKey])) {
                if ($settingsField['type'] === 'checkbox') {
                    $sanitizedInput[$fieldKey] = absint($input[$fieldKey]);
                }
                if ($settingsField['type'] === 'text' || $settingsField['type'] === 'select') {
                    $sanitizedInput[$fieldKey] = sanitize_text_field($input[$fieldKey]);
                }
                if ($settingsField['type'] === 'callback') {
                    $sanitizedInput[$fieldKey] =call_user_func_array($settingsField['sanitize_callback'], [$input[$fieldKey]]);
                }
            }
        }

        return $sanitizedInput;
    }

    /**
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter your settings below:';
    }

    /**
     * Catch callbacks for creating setting fields
     * @param string $function
     * @param array $arguments
     * @return bool
     */
    public function __call($function, $arguments)
    {
        if (!isset($this->settingsFields[$function])) {
            return false;
        }

        $field = $this->settingsFields[$function];
        $this->create_settings_field($field, $function);
    }

    public function get_setting_value($settingKey, $locale = null)
    {
        if (!$this->settingsValues) {
            $this->settingsValues = get_option(self::SETTINGS_KEY);
        }

        $settingKey = $this->get_localized_setting_key($settingKey, $locale);

        if (isset($this->settingsValues[$settingKey]) && !empty($this->settingsValues[$settingKey])) {
            return $this->settingsValues[$settingKey];
        }
        return false;
    }

    public function get_localized_setting_key($settingKey, $locale = null)
    {
        if ($locale === null && $this->languages_is_enabled()) {
            $locale = $this->get_current_locale();
        }

        if ($locale) {
            return $this->locale_to_lang_code($locale) . '_' . $settingKey;
        }

        return $settingKey;
    }

    public function get_site_id($locale = null)
    {
        return $this->get_setting_value('site_id', $locale) ?: '';
    }

    public function get_organisation_prefix($locale = null)
    {
        return $this->get_setting_value('organisation_prefix', $locale) ?: '';
    }

    public function get_brand($locale = null)
    {
        return $this->get_setting_value('brand', $locale) ?: '';
    }

    public function get_api_user($locale = null)
    {
        return $this->get_setting_value('api_user', $locale) ?: '';
    }

    public function get_api_key($locale = null)
    {
        return $this->get_setting_value('api_key', $locale) ?: '';
    }

    public function get_enabled($locale = null)
    {
        return $this->get_setting_value('enabled', $locale) === 1 ? true : false;
    }

    public function get_widget_ids($locale = null)
    {
        return $this->get_setting_value(WidgetSettings::SETTING_KEY, $locale) ?: [];
    }

    public function get_searchable_taxonomies($locale = null)
    {   // Flip array so the sort order number becomes the key ie. [1 => 'category', 2 => 'post_tag']
        $taxonomyOrder = array_flip($this->get_setting_value(CustomTaxonomiesSettings::SETTING_KEY_ORDER, $locale)) ?: [];
        ksort($taxonomyOrder); // Sort taxonomy by array key, (order number)
        // Get the searchable taxonomies
        $taxonomies = array_keys($this->get_setting_value(CustomTaxonomiesSettings::SETTING_KEY, $locale)) ?: [];
        // Intersect with order so searchable taxonomies are returned in the correct order
        return array_intersect($taxonomyOrder, $taxonomies);
    }

    private function enable_language_fields()
    {
        $languageEnabledFields = [];

        foreach ($this->get_languages() as $language) {
            foreach ($this->settingsFields as $fieldKey => $settingsField) {
                $localeFieldKey = $this->get_localized_setting_key($fieldKey, $language->locale);
                $languageEnabledFields[$localeFieldKey] = $settingsField;
                $languageEnabledFields[$localeFieldKey]['name'] .= ' ' . $language->locale;
                $languageEnabledFields[$localeFieldKey]['locale'] = $language->locale;
            }
        }

        $this->settingsFields = $languageEnabledFields;
    }

    /**
     * Returns the language code from locale ie. 'da_DK' becomes 'da'
     *
     * @param $locale
     * @return string
     */
    public function locale_to_lang_code($locale)
    {
        return substr($locale, 0, 2);
    }

    public function languages_is_enabled()
    {
        return function_exists('Pll') && PLL()->model->get_languages_list();
    }

    public function get_languages()
    {
        if ($this->languages_is_enabled()) {
            return PLL()->model->get_languages_list();
        }
        return false;
    }

    /**
     * Get the current language by looking at the current HTTP_HOST
     *
     * @return null|PLL_Language
     */
    public function get_current_language()
    {
        if ($this->languages_is_enabled()) {
            return PLL()->model->get_language(pll_current_language());
        }
        return null;
    }

    public function set_current_locale($locale)
    {
        if ($locale && !empty($locale) && $this->languages_is_enabled()) {
            $this->currentLocale = $this->locale_to_lang_code($locale);
        } else {
            $this->currentLocale = null;
        }
    }

    public function get_current_locale()
    {
        if ($this->currentLocale !== null) {
            return $this->currentLocale;
        }
        $currentLang = $this->get_current_language();
        return $currentLang ? $currentLang->locale : null;
    }

    private function get_select_field_options($field)
    {
        if (isset($field['options_callback'])) {
            $options = $this->{$field['options_callback']}($field['locale']);
            if ($options) {
                return $options;
            }
        }

        return [];
    }

    private function create_settings_field($field, $fieldKey)
    {
        $fieldName = self::SETTINGS_KEY . "[$fieldKey]";
        $fieldOutput = false;

        if ($field['type'] === 'text') {
            $fieldValue = isset($this->settingsValues[$fieldKey]) ? esc_attr($this->settingsValues[$fieldKey]) : '';
            $fieldOutput = "<input type='text' name='$fieldName' value='$fieldValue' class='regular-text' />";
        }
        if ($field['type'] === 'checkbox') {
            $checked = isset($this->settingsValues[$fieldKey]) && $this->settingsValues[$fieldKey] ? 'checked' : '';
            $fieldOutput = "<input type='hidden' value='0' name='$fieldName'>";
            $fieldOutput .= "<input type='checkbox' value='1' name='$fieldName' $checked />";
        }
        if ($field['type'] === 'select') {
            $fieldValue = isset($this->settingsValues[$fieldKey]) ? $this->settingsValues[$fieldKey] : '';
            $fieldOutput = "<select name='$fieldName'>";
            $options = $this->get_select_field_options($field);
            foreach ($options as $option) {
                $selected = ($option['system_key'] === $fieldValue) ? 'selected' : '';
                $fieldOutput .= "<option value='" . $option['system_key'] . "' $selected >" . $option['system_key'] . "</option>";
            }
            $fieldOutput .= "</select>";
        }
        if ($field['type'] === 'callback') {
            $fieldValue = isset($this->settingsValues[$fieldKey]) ? $this->settingsValues[$fieldKey] : [];

            call_user_func_array($field['callback'], [$fieldName, $fieldValue]);
        }

        if ($fieldOutput) {
            print $fieldOutput;
        }
    }
}
