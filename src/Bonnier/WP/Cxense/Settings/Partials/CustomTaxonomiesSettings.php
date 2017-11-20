<?php

namespace Bonnier\WP\Cxense\Settings\Partials;

class CustomTaxonomiesSettings
{
    const SETTING_KEY = 'custom_taxonomies';
    const SETTING_KEY_ORDER = 'custom_taxonomies_order';
    const DISABLED_TAXONOMIES = [
        'post_tag',
        'nav_menu',
        'link_category',
        'post_format',
        'language',
        'post_translations',
        'term_language',
        'term_translations',
    ];

    public static function render($fieldName, $fieldValues)
    {
        foreach (static::get_enabled_taxonomies() as $taxonomy) {
            $checked = isset($fieldValues[$taxonomy]) && $fieldValues[$taxonomy] == 1 ?  'checked' : '';
            echo "
                <strong>$taxonomy:</strong> 
                <input type='hidden' value='0' name='${fieldName}[$taxonomy]'>
                <input type='checkbox' value='1' name='{$fieldName}[$taxonomy]' $checked />
                <br>
            ";
        }
    }

    public static function renderTaxonomyOrder($fieldName, $fieldValues)
    {
        $taxonomies = static::get_enabled_taxonomies();
        $maxOrder = count($taxonomies);

        echo "<p>Give each taxonomy a number from 1 and up to order the taxonomies, lower numbers come first</p>";

        foreach ($taxonomies as $key => $taxonomy) {
            $value = $fieldValues[$taxonomy] ?? ($key + 1);
            echo "
                <strong>$taxonomy:</strong> 
                <input type='number' value='$value' min='1' max='$maxOrder' name='${fieldName}[$taxonomy]'>
                <br>
            ";
        }
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

    public static function get_printable_taxonomies() {
        $taxonomies = static::get_enabled_taxonomies();
        unset($taxonomies['category']); // Disable category as it is by default always printed
        return $taxonomies;
    }

    private static function get_enabled_taxonomies() {
        // return array values to get a numbered array rather than associative ie. ['category' => 'category']
        return array_values(array_diff(get_taxonomies(), static::DISABLED_TAXONOMIES));
    }
}
