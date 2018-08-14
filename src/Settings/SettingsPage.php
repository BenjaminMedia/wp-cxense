<?php

namespace Bonnier\WP\Cxense\Settings;

use Bonnier\Willow\MuPlugins\Helpers\AbstractSettingsPage;
use Bonnier\WP\Cxense\Settings\Partials\CustomTaxonomiesSettings;
use Bonnier\WP\Cxense\Settings\Partials\WidgetSettings;

class SettingsPage extends AbstractSettingsPage
{
    protected $settingsKey = 'wp_cxense_settings';
    protected $settingsGroup = 'wp_cxense_settings_group';
    protected $settingsSection = 'wp_cxense_settings_section';
    protected $settingsPage = 'wp_cxense_settings_page';
    protected $toolbarName = 'cXense';
    protected $title = 'WP cXense settings:';
    protected $noticePrefix= 'WP cXense:';

    protected $settingsFields = [
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
    public function getSiteId($locale = null)
    {
        return $this->getSettingValue('site_id', $locale) ?: '';
    }

    public function getOrganisationPrefix($locale = null)
    {
        return $this->getSettingValue('organisation_prefix', $locale) ?: '';
    }

    public function getBrand($locale = null)
    {
        return $this->getSettingValue('brand', $locale) ?: '';
    }

    public function getApiUser($locale = null)
    {
        return $this->getSettingValue('api_user', $locale) ?: '';
    }

    public function getApiKey($locale = null)
    {
        return $this->getSettingValue('api_key', $locale) ?: '';
    }

    public function getEnabled($locale = null)
    {
        return $this->getSettingValue('enabled', $locale) === 1 ? true : false;
    }

    public function getWidgetIds($locale = null)
    {
        return $this->getSettingValue(WidgetSettings::SETTING_KEY, $locale) ?: [];
    }

    public function getPersistedQueryId($locale = null)
    {
        return $this->getSettingValue('persisted_query_id', $locale) ?: '';
    }

    public function getSearchableTaxonomies($locale = null)
    {
        // Flip array so the sort order number becomes the key ie. [1 => 'category', 2 => 'post_tag']
        $taxonomyOrder = array_flip(
            $this->getSettingValue(CustomTaxonomiesSettings::SETTING_KEY_ORDER, $locale)
        ) ?: [];

        // Sort taxonomy by array key, (order number)
        ksort($taxonomyOrder);

        // Get the searchable taxonomies
        $taxonomies = array_keys($this->getSettingValue(CustomTaxonomiesSettings::SETTING_KEY, $locale)) ?: [];

        // Intersect with order so searchable taxonomies are returned in the correct order
        return array_intersect($taxonomyOrder, $taxonomies);
    }

    public function getSortbyWidgetId($locale = null)
    {
        return $this->getSettingValue('sortby_widget_id', $locale);
    }

    public function enableQueryCache($locale = null)
    {
        return $this->getSettingValue('enable_query_cache', $locale);
    }
}
