<?php
/**
 * Contains functions for all the fields that are rendered in a CPT. 
 * Over time all fields will be moved here.
 */
namespace Dusty_Sun\WP_CPT_API\v1_4\CPT_Builder;
// This parent class cannot do anything on its own - must be extended by a child class
if(!class_exists('Dusty_Sun\WP_CPT_API\v1_4\CPTBuilder\CPT_RepeaterFields'))  { 
    class CPT_RepeaterFields {

        // public function __construct(){

        // }

        /** 
         * Standard text field
         */
        public static function render_multifield_repeater($id, $class, $values_array, $parent_field) {

            // set it to an array if not
            if(!(is_array($values_array))) {
                $values_array = array('');
            }
            // wl($fields);
            $index = 0;
            $html = '<div class="ds-wp-cpt-repeater" data-id="' . $id. '">';
            foreach($values_array as $value_item) {
                $html .= '<div class="ds-wp-cpt-repeater-item"><button class="ds-wp-cpt-repeater-item-remove" tabindex="-1">Remove Item</button><span class="ds-wp-cpt-repeater-item-move"></span>';

                foreach($parent_field['fields'] as $field) {
                    $field_id = $id . '[' . $index . '][' . $field['id'] . ']';

                    $html .= '<div class="ds-wp-cpt-metabox-field-wrapper ' . $field['type'] . ' ' . $field['id'] . '">';

                    $value = isset($values_array[$index][$field['id']]) ? $values_array[$index][$field['id']] : '';
                    if($field['type'] == 'text') {
                        
                        $help_text = isset($field['help_text']) ? $field['help_text'] : '';
                        $html .= CPT_InputFields::render_label_standard($field_id, $field['label'], '', $help_text);
                        $html .= CPT_InputFields::render_input_text($field_id, $class, $value);
                    }
                    if($field['type'] == 'image') {
                        $html .= CPT_InputFields::render_image_input($field_id, $value);
                    }
                    if($field['type'] == 'media') {
                        $mime_type = isset($field['mime_type']) ? $field['mime_type'] : 'all';
                        $html .= CPT_InputFields::render_media_input($field_id, $value, $mime_type);
                    }
                    $html .= '</div> <!-- .ds-wp-cpt-metabox-field-wrapper -->';
                }
                $html .= '</div> <!-- .ds-wp-cpt-repeater-item -->';
                $index++;
            } // end values_array

            // $html = ' <input type="text" class="' . $class . '" name="'. $id . '" id="'. $id .'" value="'. $value . '" size="30" style="width:100%" ' . $readonly . '/>';
            $html .= '<button class="ds-wp-cpt-repeater-add">Add Item</button></div> <!-- .ds-wp-cpt-repeater -->';

            return $html;
        }

    }
}

