<?php

namespace Bonnier\WP\Cxense\Settings\Partials;

class CustomTaxonomiesSettings
{
    const SETTING_KEY = 'custom_taxonomies';

    public static function render($fieldName, $fieldValues)
    {
        $taxonomies = get_taxonomies();
        $disabledTypes = ['category', 'post_tag'];
        $output = "";

        foreach ($taxonomies as $taxonomy) {
            $checked = isset($fieldValues[$taxonomy]) && $fieldValues[$taxonomy] == 1 ?  'checked' : '';
            if (!in_array($taxonomy, $disabledTypes)) {
                $output .= "
                    <strong>$taxonomy:</strong> 
                    <input type='hidden' value='0' name='".$fieldName."[$taxonomy]'>
                    <input type='checkbox' value='1' name='".$fieldName."[$taxonomy]' $checked />
                    <br>
                ";
            }
        }

        echo $output;
    }

    public static function sanitize_input($inputValues)
    {
        $sanitizedInput = [];

        foreach ($inputValues as $key => $value) {
            if (sanitize_text_field($value)) {
                $sanitizedInput[$key] = $value;
            }
        }

        return $sanitizedInput;
    }
}
