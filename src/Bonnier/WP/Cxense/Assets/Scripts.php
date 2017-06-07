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

        $org_prefix_setting = self::$settings->get_organisation_prefix();
        $this->org_prefix = $org_prefix_setting ? $org_prefix_setting . '-' : '';

        // Filter to add custom cxense meta tags if needed
        add_filter('cxense_head_tags', [$this, 'head_tags'], 5);
        add_action('wp_head', [$this, 'add_head_tags']);
        add_action('wp_footer', [$this, 'add_cxense_script']);
    }

    public function head_tags()
    {
        $recs_tags = [];

        $locale = explode('_', get_locale());
        $recs_tags[$this->org_prefix . 'country'] = $locale[1];
        $recs_tags[$this->org_prefix . 'language'] = strtoupper($locale[0]);

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
                $recs_tags[$this->org_prefix .'bod-taxo-cat-url'] = get_category_link($this->get_category()->cat_ID);
            }

            if (get_the_tags($post->ID)) {
                $recs_tags[$this->org_prefix . 'taxo-tag'] = $this->get_post_tags($post);
            }
        }

        // Tell cXense wether the current page is a front page or an article
        $recs_tags['pageclass'] = is_front_page() ? 'frontpage' : 'article';

        return $recs_tags;
    }

    private function get_post_tags()
    {
        $tags = get_the_tags();
        if (!is_wp_error($tags)) {
            $data = [];
            foreach (get_the_tags() as $tag) {
                $data[] = $tag->name;
            }

            return $data;
        }
        return '';
    }

    public function add_head_tags()
    {
        $recs_tags = apply_filters('cxense_head_tags', []);

        foreach ($recs_tags as $name => $val) {
            if( is_array($val)) {
                foreach ($val as $sub_name => $sub_value) {
                    echo '<meta name="cXenseParse:'.$name.'" content="'.$sub_value.'" />'.PHP_EOL;
                }
            } else {
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

    private function get_root_category($catid)
    {
        $catParent = null;
        while ($catid) {
            $cat = get_category($catid);
            $catid = $cat->category_parent;
            $catParent = $cat->name;
        }

        return $catParent;
    }

    private function get_category()
    {
        $category = get_the_category();
        return $category ? $category[0] : '';
    }
}