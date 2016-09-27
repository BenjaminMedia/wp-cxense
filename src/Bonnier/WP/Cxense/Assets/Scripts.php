<?php

namespace Bonnier\WP\Cxense\Assets;

use Bonnier\WP\Cxense;
use Bonnier\WP\Cxense\Plugin;
use Bonnier\WP\Cxense\Settings\SettingsPage;

class Scripts
{

    private static $settings;

    public static function bootstrap(SettingsPage $settings)
    {
        self::$settings = $settings;

        add_action('wp_head', [__CLASS__, 'add_head_tags']);
        add_action('wp_footer', [__CLASS__, 'add_cxense_script']);
    }

    public static function add_head_tags()
    {
        $recs_tags = [];

        if ( is_singular() || is_single() ) {

            global $post;

            $org_prefix_setting = self::$settings->get_organisation_prefix();

            // Get organisation prefix
            $org_prefix = $org_prefix_setting ? $org_prefix_setting . '-' : '';

            // Set the ID
            $recs_tags['cXenseParse:recs:articleid'] = $post->ID;

            // Set the pagetype
            $recs_tags['cXenseParse:' . $org_prefix . 'pagetype'] = $post->post_type;

            // Set the publish time
            $recs_tags['cXenseParse:recs:publishtime'] = date('c', strtotime($post->post_date));

        }

        // Tell cXense wether the current page is a front page or an article
        $recs_tags['cXenseParse:pageclass'] = is_front_page() ? 'frontpage' : 'article';

        foreach($recs_tags as $name => $val) {
            echo '<meta name="'.$name.'" content="'.$val.'" />'.PHP_EOL;
        }
    }

    public static function add_cxense_script()
    {

        if( self::$settings->get_enabled() ) {

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
}