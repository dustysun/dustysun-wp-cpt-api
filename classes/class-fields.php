<?php
/**
 * Contains functions for all the fields that are rendered in a CPT. 
 * Over time all fields will be moved here.
 */
namespace Dusty_Sun\WP_CPT_API\v1_4;
// This parent class cannot do anything on its own - must be extended by a child class
if(!class_exists('Dusty_Sun\WP_CPT_API\v1_4\CPTBuilder\CPTFields'))  { 
    class CPTFields {

        // public function __construct(){

        // }

        public static function render_input_text($values = array()) {

            
            echo $standardFieldLabel;
            $html = ' <input type="text" class="' . $field_class . '" name="'. $field['id']. '" id="'. $field['id'] .'" value="'. $value_shown . '" size="30" style="width:100%" ' . $readonly . '/>';

        }

    }
}

