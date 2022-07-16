<?php
/**
 * Contains functions for all the fields that are rendered in a CPT. 
 * Over time all fields will be moved here.
 */
namespace Dusty_Sun\WP_CPT_API\v1_4\CPT_Builder;
// This parent class cannot do anything on its own - must be extended by a child class
if(!class_exists('Dusty_Sun\WP_CPT_API\v1_4\CPTBuilder\CPT_InputFields'))  { 
    class CPT_InputFields {

        // public function __construct(){

        // }
        /**
         * Standard label
         */
        public static function render_label_standard($id, $label, $class = '', $help_text = '') {
          $html = '<div class="ds-wp-cpt-metabox-settings-label"><label for="'. $id .'" class="' . $class . '">'. $label . '</label><div class="ds-wp-cpt-metabox-settings-help-text">' . $help_text . '</div></div>';
          return $html;
        }

        /** 
         * Standard text field
         */
        public static function render_input_text($id, $class, $value, $readonly = false, $placeholder = '') {
            if($readonly || $readonly == 'readonly') {
              $readonly = 'readonly'; 
            } else {
              $readonly = '';
            }
            $html = '<div class="ds-wp-cpt-metabox-settings-field"><input type="text" class="' . $class . '" placeholder="' . $placeholder .'" name="'. $id . '" id="'. $id .'" value="'. $value . '" size="30" style="width:100%" ' . $readonly . '/></div>';

            return $html;
        }

        /**
         * Media input option
         */

         public static function render_media_input($id, $value, $mime_type = 'all') {

            // See if there's a media id already saved as post meta
            $ds_wp_cpt_attachment_media_id = $value; 

            // Get the media src
            $ds_wp_cpt_attachment_media_src = wp_get_attachment_url( $ds_wp_cpt_attachment_media_id, true);

            $ds_wp_cpt_attachment_media_img_src = '';
            if($ds_wp_cpt_attachment_media_src != '') {
              // Get the image src
              $ds_wp_cpt_attachment_media_img_src = wp_get_attachment_image_src( $ds_wp_cpt_attachment_media_id, 'thumbnail', true );
            } // end if

            // For convenience, see if the array is valid
            $ds_wp_cpt_attachment_media_have_img = is_array( $ds_wp_cpt_attachment_media_img_src );

                        // add a class to the removable div to show hover effects 
            $removeable_class = '';
            if($ds_wp_cpt_attachment_media_have_img) $removeable_class = 'has-media';
            $html = '<div class="ds-wp-cpt-metabox-settings-field">';
            $html .= '<div class="ds-wp-cpt-uploader ds-wp-cpt-media-uploader">';
            $html .= '<div class="ds-wp-cpt-uploader-removable ' . $removeable_class . '">';
            $html .= '<div class="ds-wp-cpt-remove"><span class="fa-stack"><i class="fa fa-circle fa-stack-1x icon-background"></i><i class="fa fa-times-circle fa-stack-1x"></i></span></div>';
            $html .= '<input name="' . $id . '" id="' . $id . '" class="ds-wp-cpt-uploader-value" type="hidden"  value="'. $value . '" />';
            $html .= '<div id="' . $id . '-img-container" class="ds-wp-cpt-image-uploader-image-container">';
            if ( $ds_wp_cpt_attachment_media_img_src != '' ) {
              $html .= '<img src="' . $ds_wp_cpt_attachment_media_img_src[0] . '" alt="" style="max-width:100%;" />';
            } // end if ( $ds_wp_cpt_attachment_media_img_src ) 
            $html .= '</div>';              
            $html .= '<p id="' . $id . '-file-name" class="ds-wp-cpt-file-name">' . $ds_wp_cpt_attachment_media_src . '</p>';
            $html .= '<button id="' . $id. '_button" class="ds-wp-cpt-upload-button button" data-mime_type="' . $mime_type . '"/><span class="no-image">Upload Media</span><span class="has-image">Change Media</span></button>';
            $html .= '</div> <!-- .ds-wp-cpt-uploader-removable -->';
            $html .= '</div> <!-- .ds-wp-cpt-uploader -->';
            $html .= '</div> <!-- .ds-wp-cpt-metabox-settings-field -->';

            return $html;
         }

         /**
          * Image input option
          */

        public static function render_image_input($id, $value) {
             // Get WordPress' media upload URL
             $upload_link = esc_url( get_upload_iframe_src( 'image', $id ) );

             // See if there's a media id already saved as post meta
             $ds_wp_cpt_attachment_img_id =  $value;

             // Get the image src
             $ds_wp_cpt_attachment_img_src = wp_get_attachment_image_src( $ds_wp_cpt_attachment_img_id, 'full' );

             // For convenience, see if the array is valid
             $ds_wp_cpt_attachment_have_img = is_array( $ds_wp_cpt_attachment_img_src );

             // add a class to the removable div to show hover effects 
             $removeable_class = '';
             if( $ds_wp_cpt_attachment_have_img ) $removeable_class = 'has-image';
             
             $html = '<div class="ds-wp-cpt-image-uploader ds-wp-cpt-uploader">';
             $html .= '<div class="ds-wp-cpt-image-uploader-removable ' . $removeable_class . '">';
             $html .= '<div class="ds-wp-cpt-remove"><span class="fa-stack"><i class="fa fa-circle fa-stack-1x icon-background"></i><i class="fa fa-times-circle fa-stack-1x"></i></span></div>';
             $html .= '<div id="' . $id . '-img-container" class="ds-wp-cpt-image-uploader-image-container">';
             if ( $ds_wp_cpt_attachment_have_img ) {
               $html .= '<img src="' . $ds_wp_cpt_attachment_img_src[0] . '" alt="" style="max-width:100%;" />';
             } // end if ( $ds_wp_cpt_attachment_have_img ) 
             $html .= '</div>';

             $html .= '<!-- A hidden input to set and post the chosen image id -->
                   <input name="' . $id . '" id="' . $id . '"  class="ds-wp-cpt-image-uploader-value" type="hidden"  value="'. $value . '"/>';
             $html .= '<button id="' . $id . '_button" class="ds-wp-cpt-upload-button button" /><span class="no-image">Upload Image</span><span class="has-image">Change Image</span></button>';
             $html .= '</div>';
             $html .= '</div>';

             return $html;
        }

        /**
         * Radio input option
         */
        public static function render_radio_input($id, $options, $class, $value, $readonly = false) {

            if($readonly == true) {
              $radio_readonly = ' disabled="disabled"';
            } else {
              $radio_readonly = '';
            }
            //Set a counter for how many items there are
            //If this is the first item, we'll check it in case there
            //are no items actually checked
            $radioCounter = 1;

            //Get the selected or default value, if any
            $checked_value = $value;

            $html = '<div class="ds-wp-cpt-metabox-settings-field ds-wp-cpt-radio">';
            foreach ($options as $radioKey => $option) {

              if($checked_value == $radioKey || $radioCounter == 1 ) {
                $checked_html = ' checked="checked"';
              } else {
                $checked_html = '';
              }
              $html .= '<span class="ds-wp-cpt-radio-item"><input type="radio" value="'. $radioKey .'" class="'. $class . '" name="'. $id .'" id="' . $radioKey . '_' . $id .   '"' . $checked_html . $radio_readonly . '/> <label for="' . $radioKey . '_' . $id . '">'.$option.'</label></span>';
              //increase the radioCounter
              $radioCounter++;
            }
            $html .= '</div>';
            return $html;

        }
        /**
         * Checkbox input option
         */
        public static function render_checkbox_input($id, $options, $class, $value, $readonly = false) {

          if($readonly == true) {
            $checkbox_readonly = ' disabled="disabled"';
          } else {
            $checkbox_readonly = '';
          }
          //Set a counter for how many items there are
          //If this is the first item, we'll check it in case there
          //are no items actually checked
          $checkboxCounter = 1;

          //Get the selected or default value, if any
          $checked_value = $value;

          $html = '<div class="ds-wp-cpt-metabox-settings-field ds-wp-cpt-check">';
          foreach ($options as $checkKey => $option) {

            if($checked_value == $checkKey) {
              $checked_html = ' checked="checked"';
            } else {
              $checked_html = '';
            }
            $html .= '<span class="ds-wp-cpt-check-item"><input type="checkbox" value="'. $checkKey .'" class="'. $class . '" name="'. $id .'" id="' . $checkKey . '_' . $id .   '"' . $checked_html . $checkbox_readonly . '/> <label for="' . $checkKey . '_' . $id . '">'.$option.'</label></span>';
            //increase the checkboxCounter
            $checkboxCounter++;
          }
          $html .= '</div>';
          return $html;

      }
    }
}

