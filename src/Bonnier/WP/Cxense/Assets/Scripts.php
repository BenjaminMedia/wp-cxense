<?php

namespace Bonnier\WP\Cxense\Assets;

use Bonnier\WP\Cxense\Settings\SettingsPage;

class Scripts
{
    /**
     * @var static SettingsPage
     */
    private static $settings;

    /**
     * @var string Organization prefix
     */
    private $org_prefix;

    public function bootstrap(SettingsPage $settings)
    {
        self::$settings = $settings;

        // Filter to add custom cxense meta tags if needed
        add_filter('cxense_head_tags', [$this, 'head_tags'], 5);
        add_action('wp_head', [$this, 'add_head_tags']);
        add_action('wp_footer', [$this, 'add_cxense_script']);
    }

    public function head_tags()
    {
        $org_prefix_setting = self::$settings->get_organisation_prefix();
        $this->org_prefix = $org_prefix_setting ? $org_prefix_setting . '-' : '';

        $recs_tags = [];

        $locale = explode('_', get_locale());
        $recs_tags[$this->org_prefix . 'country'] = (strtoupper($locale[1] ?? $locale[0]));
        $recs_tags[$this->org_prefix . 'language'] = strtoupper($locale[0]);

        if(self::$settings->get_brand()) {
            $recs_tags[$this->org_prefix . 'brand'] = self::$settings->get_brand();
        }

        if ( is_singular() || is_single() ) {

            global $post;

            // Set the ID
            $recs_tags['recs:articleid'] = $post->ID;

            // Set the pagetype
            $recs_tags[$this->org_prefix . 'pagetype'] = $post->post_type;

            // Set the publish time
            $recs_tags['recs:publishtime'] = date('c', strtotime($post->post_date));

            if ($this->get_category()) {

                $recs_tags[$this->org_prefix . 'taxo-cat'] = $this->get_category()->name;
                $recs_tags[$this->org_prefix . 'taxo-cat-top'] = $this->get_category()->name;

                // Override the previous meta value if category parent exists
                if ($this->get_category()->parent) {
                    $recs_tags[$this->org_prefix . 'taxo-cat-top'] = $this->get_root_category($this->get_category()->cat_ID);
                }

                // Current category link to listpage
                $recs_tags[$this->org_prefix .'taxo-cat-url'] = get_category_link($this->get_category()->cat_ID);
            }

            // This post type requires acf. Therefor we don't check if it's installed
            if($post->post_type === 'contenthub_composite') {
                $fields = get_fields($post);

                //Override current pagetype with the correct one from the composite
                $recs_tags[$this->org_prefix . 'pagetype'] = $fields['kind'];

                if(!empty($fields['editorial_type'])) {
                    $recs_tags[$this->org_prefix . 'taxo-editorialtype'] = $this->objects_to_array($fields['editorial_type']);
                }

                if(!empty($fields['difficulty'])) {
                    $recs_tags[$this->org_prefix . 'taxo-difficulty'] = $this->objects_to_array($fields['difficulty']);
                }
            }

            if (get_the_tags($post->ID)) {
                $recs_tags[$this->org_prefix . 'taxo-tag'] = $this->objects_to_array(get_the_tags());
            }
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
            if(!is_object($items) && count($items) === 1) {
                return $items[0]->name;
            }
            $data = [];
            
            // More than one? Start the loop!
            foreach ($items as $item) {
                if(isset($item->name)) {
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
        if ( self::$settings->get_enabled() ) {

            $siteId = self::$settings->get_site_id();

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
}
