<?php

namespace Bonnier\WP\Cxense\Assets;

use Bonnier\WP\Cxense\Settings\Partials\CustomTaxonomiesSettings;
use Bonnier\WP\Cxense\Settings\SettingsPage;
use Bonnier\WP\Cxense\WpCxense;

class Scripts
{
    /**
     * @var string Organization prefix
     */
    private $org_prefix;

    public function bootstrap()
    {
        // Filter to add custom cxense meta tags if needed
        add_filter('cxense_head_tags', [$this, 'head_tags'], 5);
        add_action('wp_head', [$this, 'add_head_tags']);
        add_action('wp_footer', [$this, 'add_cxense_script']);
    }

    public function head_tags()
    {
        $org_prefix_setting = WpCxense::instance()->settings->getOrganisationPrefix();
        $this->org_prefix = $org_prefix_setting ? $org_prefix_setting . '-' : '';

        $recs_tags = [];

        $locale = explode('_', get_locale());
        $recs_tags[$this->org_prefix . 'country'] = (strtoupper(isset($locale[1]) ? $locale[1] : $locale[0]));
        $recs_tags[$this->org_prefix . 'language'] = strtoupper($locale[0]);

        if ($brand = WpCxense::instance()->settings->getBrand()) {
            $recs_tags[$this->org_prefix . 'brand'] = $brand;
        }

        $recs_tags[$this->org_prefix . 'pagetype'] = $this->get_pagetype();

        $recs_tags['recs:recommendable'] = $this->get_recommendable();

        if (is_singular() && is_single()) {
            global $post;

            // Set the ID
            $recs_tags['recs:articleid'] = $post->ID;

            // Set the pagetype
            $recs_tags[$this->org_prefix . 'pagetype'] = $this->get_page_type($post);

            // Set the publish time
            $recs_tags['recs:publishtime'] = date('c', strtotime($post->post_date));

            if ($this->get_category()) {
                $recs_tags[$this->org_prefix . 'taxo-cat'] = $this->get_category()->name;
                $recs_tags[$this->org_prefix . 'taxo-cat-top'] = $this->get_category()->name;

                // Override the previous meta value if category parent exists
                if ($this->get_category()->parent) {
                    $recs_tags[$this->org_prefix . 'taxo-cat-top'] = $this->get_root_category(
                        $this->get_category()->cat_ID
                    );
                }

                // Current category link to listpage
                $recs_tags[$this->org_prefix .'taxo-cat-url'] = get_category_link($this->get_category()->cat_ID);
            }

            if (get_the_tags($post->ID)) {
                $recs_tags[$this->org_prefix . 'taxo-tag'] = $this->objects_to_array(get_the_tags());
            }

            $recs_tags = $this->get_custom_taxonomy_terms($post->ID, $recs_tags);
        }

        // The date is just a fallback, and is the day it was coded
        $recs_tags['metatag-changedate'] = getenv('CXENSE_CHANGEDATE') ?: '07062017';

        // Tell cXense wether the current page is a front page or an article
        $recs_tags['pageclass'] = is_front_page() ? 'frontpage' : 'article';

        return $recs_tags;
    }

    /**
     * @param $items
     * @return array|string
     */
    private function objects_to_array($items)
    {
        if (!is_wp_error($items) && !empty($items)) {
            // Only one? Just return it
            if (!is_object($items) && count($items) === 1) {
                return $items[0]->name;
            }
            $data = [];

            // More than one? Start the loop!
            foreach ($items as $item) {
                if (isset($item->name)) {
                    $data[] = $item->name;
                }
            }

            return $data;
        }
        return '';
    }

    public function add_head_tags()
    {
        $recs_tags = apply_filters('cxense_head_tags', []);

        $this->recursive_get_meta_tag($recs_tags);

        $this->persisted_query_id_tag();
    }

    private function persisted_query_id_tag()
    {
        if ($persisted_query_id = WpCxense::instance()->settings->getPersistedQueryId()) {
            echo '<meta name="cxense-persisted-query-id" content="' . $persisted_query_id . '"></meta>' . PHP_EOL;
        }
    }

    private function recursive_get_meta_tag($recs_tags, $org_cxense_key = '')
    {
        foreach ($recs_tags as $name => $val) {
            if (is_array($val)) {
                $array = array_values($val);
                $this->recursive_get_meta_tag($array, $name);
            } else {
                $name = (!is_numeric($name) && !$org_cxense_key) ? $name : $org_cxense_key;
                echo '<meta name="cXenseParse:'.$name.'" content="'.$val.'" />'.PHP_EOL;
            }
        }
    }

    public function add_cxense_script()
    {
        if (WpCxense::instance()->settings->getEnabled()) {
            $siteId = WpCxense::instance()->settings->getSiteId();

            $script = "
            <!-- Cxense script begin -->
            <script type=\"text/javascript\">
                
                // Setup cXense gloabal vars
                var cX = cX || {}; cX.callQueue = cX.callQueue || [];
                cX.callQueue.push(['setSiteId', '$siteId']);
                cX.callQueue.push(['sendPageViewEvent']);
            
                // Add cXense script 
                (function(d,s,e,t){e=d.createElement(s);e.type='text/java'+s;e.async='async';
                    e.src='http'+('https:'===location.protocol?'s://s':'://')+'cdn.cxense.com/cx.js';
                    t=d.getElementsByTagName(s)[0];t.parentNode.insertBefore(e,t);})(document,'script');
            </script>
            <!-- Cxense script end -->
            ";

            echo $script;
        }
    }

    private function get_root_category($category_id)
    {
        $category_parent_name = null;
        while ($category_id) {
            $cat = get_category($category_id);
            $category_id = $cat->category_parent;
            $category_parent_name = $cat->name;
        }

        return $category_parent_name;
    }

    private function get_category()
    {
        $category = get_the_category();
        return $category ? $category[0] : '';
    }

    private function get_custom_taxonomy_terms($postId, $recs_tags)
    {
        $customTaxonomies = CustomTaxonomiesSettings::get_printable_taxonomies();

        foreach ($customTaxonomies as $customTaxonomy) {
            $customTaxonomyKey = $this->org_prefix . 'taxo-' . str_replace('_', '-', $customTaxonomy);

            if ($customTaxonomyTerms = wp_get_post_terms($postId, $customTaxonomy)) {
                $recs_tags[$customTaxonomyKey] = [];
            }

            foreach ($customTaxonomyTerms as $term) {
                array_push($recs_tags[$customTaxonomyKey], $term->name);
            }
        }

        return $recs_tags;
    }

    private function get_page_type($post)
    {
        if (function_exists('get_field') && $post->post_type === 'contenthub_composite') {
            return get_field('kind', $post->ID);
        }
        return 'Article';
    }

    private function get_pagetype()
    {
        if (is_category()) {
            return 'category';
        }

        if (is_tag()) {
            return 'tag';
        }

        if (is_front_page()) {
            return 'frontpage';
        }

        if (is_page()) {
            return 'panel';
        }

        return '';
    }

    private function get_recommendable()
    {
        // Ops! It should return strings, not bool!
        if (is_front_page() || is_tag() || is_category() || is_page()) {
            return "false";
        }

        return "true";
    }
}
