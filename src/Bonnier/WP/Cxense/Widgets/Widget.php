<?php

namespace Bonnier\WP\Cxense\Widgets;

use Bonnier\WP\Cxense\Services\CxenseAPI;
use Bonnier\WP\Cxense\Settings\SettingsPage;

class Widget
{
    public static function render (SettingsPage $settingsPage, $width, $height) {

        if($widgetId = self::get_widget_id($settingsPage)) {

            echo "
                <!-- cXense widget begin -->
                <div id=\"$widgetId\" style=\"display:none\"></div>
                
                <script type=\"text/javascript\">
                  var cX = cX || {}; cX.callQueue = cX.callQueue || [];
                  cX.callQueue.push(['insertWidget',{
                      widgetId: '$widgetId',
                      insertBeforeElementId: '$widgetId',
                      renderTemplateUrl: 'auto',
                      width: $width, height: $height
                  }]);
                </script>
                <!-- cXense widget end -->
            ";

        }

        return null;
    }

    public static function get_widget_data(SettingsPage $settingsPage) {


        if ($widgetId = self::get_widget_id($settingsPage)) {

            if($widgetData = CxenseAPI::get_widget_data($widgetId)) {
                return $widgetData;
            }
        }

        return null;
    }

    private static function get_widget_id(SettingsPage $settingsPage) {

        if (is_singular() || is_single()) {

            $widgetIds = $settingsPage->get_widget_ids();

            global $post;

            if (isset($widgetIds[$post->post_type])) {

                return $widgetIds[$post->post_type];

            }

            return null;
        }
    }
}