<?php
/**
 * Contains functions for all the repeater fields that are rendered in a CPT. 
 * 
 * v1.6.0 
 * 
 */
namespace Dusty_Sun\WP_CPT_API\v1_4\CPTBuilder;
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
            if(isset($parent_field['lazy_load']) && $parent_field['lazy_load'] == 'true') {
                $lazy_load = true;
            } else {
                $lazy_load = false;
            } // end if lazy_load 
            $index = 0;
            $html = '<div class="ds-wp-cpt-repeater" data-id="' . $id. '">';
            foreach($values_array as $value_item) {
                $html .= '<div class="ds-wp-cpt-repeater-item"><button class="ds-wp-cpt-repeater-item-remove" tabindex="-1">Remove Item</button><span class="ds-wp-cpt-repeater-item-move"></span>';

                foreach($parent_field['fields'] as $field) {
                    $field_id = $id . '[' . $index . '][' . $field['id'] . ']';
                    $help_text = isset($field['help_text']) ? $field['help_text'] : '';
                    if(isset($field['label']) ) {
                        $label = $field['label'];
                    } else {
                        $label = '';
                    } // end if label 
                    if(isset($field['readonly']) && $field['readonly'] == 'true') {
                        $readonly = true;
                    } else {
                        $readonly = false;
                    } // end if readonly 
                    if(isset($field['placeholder'])) {
                        $placeholder = $field['placeholder'];
                    } else {
                        $placeholder = '';
                    } // end if placeholder 
 
                    $html .= '<div class="ds-wp-cpt-metabox-field-wrapper ' . $field['type'] . ' ' . $field['id'] . '">';

                    $value = isset($values_array[$index][$field['id']]) ? $values_array[$index][$field['id']] : '';
                    if($field['type'] == 'checkbox') {
                        $html .= CPT_InputFields::render_label_standard($field_id, $label, '', $help_text);
                        $html .= CPT_InputFields::render_checkbox_input($field_id, $field['options'], $class, $value, $readonly);
                    }
                    if($field['type'] == 'radio') {
                        $html .= CPT_InputFields::render_label_standard($field_id, $label, '', $help_text);
                        $html .= CPT_InputFields::render_radio_input($field_id, $field['options'], $class, $value, $readonly);
                    }
                    if($field['type'] == 'text') {
                        $html .= CPT_InputFields::render_label_standard($field_id, $label, '', $help_text);
                        $html .= CPT_InputFields::render_input_text($field_id, $class, $value, $readonly, $placeholder);
                    }
                    if($field['type'] == 'image') {
                        $html .= CPT_InputFields::render_image_input($field_id, $value, $lazy_load);
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

            $html .= '<div class="ds-wp-cpt-repeater-add-wrapper"><button class="ds-wp-cpt-repeater-add">Add Item</div></button></div> <!-- .ds-wp-cpt-repeater -->';

            return $html;
        }

    }
}

