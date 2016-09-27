<?php

namespace Bonnier\WP\Cxense\Settings\Partials;

class WidgetSettings
{
    const SETTING_KEY = 'widget_ids';

    public static function render($fieldName, $fieldValues) {
        
        $postTypes = get_post_types();
        $disabledTypes = ['attachment', 'nav_menu_item'];
        $output = "";

        foreach ($postTypes as $postType) {

            $value = isset($fieldValues[$postType]) ? $fieldValues[$postType] : '';

            if(!in_array($postType, $disabledTypes)) {
                $output .= "
                    <p><strong>CX Widget ID Type:</strong> $postType </p>
                    <input type='text' name='".$fieldName."[$postType]' value='$value' >
                    <br>
                    
                ";
            }

        }

        echo $output;

    }

    public static function sanitize_input($inputValues) {

        $sanitizedInput = [];

        foreach ($inputValues as $key => $value) {
            if(sanitize_text_field($value)) {
                $sanitizedInput[$key] = $value;
            }
        }

        return $sanitizedInput;
    }
}