<?php

namespace Bonnier\WP\Cxense\Widgets;

use Bonnier\WP\Cxense\Services\CxenseApi;
use Bonnier\WP\Cxense\Settings\SettingsPage;

class Widget
{
    public static function render(SettingsPage $settingsPage)
    {
        if ($widgetId = self::get_widget_id($settingsPage)) {
            echo "
                <!-- cXense widget begin -->
                
                <div id=\"cx_$widgetId\"></div>
                <div id=\"cx_temp_$widgetId\" style=\"display: none;\"></div>
                <script type = \"text/javascript\" > 
                    var cX = cX || {}; cX.callQueue = cX.callQueue || [];
                    var cxwid = '$widgetId';
                    cX.callQueue.push([
                        'insertWidget',
                         { 	
                            widgetId: cxwid,
                            renderFunction: function(data, context) {
                                document.getElementById('cx_temp_' + cxwid).innerHTML = data.response.template;
                                cX.renderTemplate('cx_temp_' + cxwid, 'cx_' + cxwid, data, context); 		
                            }.bind(cxwid)
                        }
                    ]);
                </script>
               
                <!-- cXense widget end -->
            ";
        }

        return null;
    }

    public static function get_widget_data(SettingsPage $settingsPage)
    {
        if ($widgetId = self::get_widget_id($settingsPage)) {
            if ($widgetData = CxenseApi::get_widget_data($widgetId)) {
                return $widgetData;
            }
        }

        return null;
    }

    private static function get_widget_id(SettingsPage $settingsPage)
    {
        if (is_singular() || is_single()) {
            $widgetIds = $settingsPage->getWidgetIds();

            global $post;

            if (isset($widgetIds[$post->post_type])) {
                return $widgetIds[$post->post_type];
            }

            return null;
        }
    }
}
