<?php
// GitHub: N/A
// Version 1.3.8
// Author: Steve Talley
// Organization: Dusty Sun
// Author URL: https://dustysun.com/

namespace Dusty_Sun\WP_CPT_API\v1_3;
// This parent class cannot do anything on its own - must be extended by a child class
if(!class_exists('Dusty_Sun\WP_CPT_API\v1_3\CPTBuilder'))  { class CPTBuilder {

  // $meta_box_fields must be set by a child by calling set_meta_box_fields
  private $meta_box_fields;

  // $custom_post_type must be set by a child by calling set_custom_post_type
  protected $custom_post_type;

  // validation errors
  private $cpt_wp_error;

  // used for validations
  protected $current_post_id;
  protected $wp_user_id;

  public function __construct(){
    add_action('admin_enqueue_scripts', array($this, 'register_ds_wp_cpt_api_admin_scripts'));

    // admin notices / errors
    add_action('admin_notices', array($this, 'ds_wp_cpt_error_messages'));

    // Add the meta box fields
    add_action('add_meta_boxes_' . $this->custom_post_type, array($this,'ds_wp_cpt_api_add_meta_boxes'), 99, 2);

    // Allow file uploads
    add_action('post_edit_form_tag', array($this, 'ds_wp_cpt_api_update_edit_form'));

    //Save the CPT data
    add_action('save_post_' . $this->custom_post_type,  array($this,'ds_wp_cpt_api_save_data'));

  } //end function __construct

  // Logging function 
  public function wl ( $log )  {
    if ( true === WP_DEBUG ) {
        if ( is_array( $log ) || is_object( $log ) ) {
            error_log( print_r( $log, true ) );
        } else {
            error_log( $log );
        }
    }
  } // end public function wl 
  public function define_meta_box_fields($post_id = null) {
    // Abstract function to be replaced by a child class function
    return null;
  }
  public function set_meta_box_fields($post_id = null) {
    $this->meta_box_fields = $this->define_meta_box_fields($post_id);
  } //end function set_meta_box_fields

  public function set_custom_post_type($custom_post_type) {
    $this->custom_post_type = $custom_post_type;
  } //end function set_custom_post_type

  //Allow the CPT form to have file uploads
  public function ds_wp_cpt_api_update_edit_form() {
      echo ' enctype="multipart/form-data"';
  } // end update_edit_form

  public function register_ds_wp_cpt_api_admin_scripts($hook) {
    // only load these scripts on the appropriate CPT edit screen
    $screen = get_current_screen();
    if( is_object($screen) && $this->custom_post_type == $screen->post_type ) {

      // Google fonts
      wp_enqueue_style('ds-wp-google-fonts-open-sans', 'https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700');
      
      // load the css
      wp_enqueue_style('ds-wp-cpt-api', plugins_url('/css/ds-wp-cpt-api-admin.css', __FILE__));

      // Load the datepicker script (pre-registered in WordPress).
      wp_enqueue_script( 'jquery-ui-datepicker' );

      //jQuery UI theme css file
      wp_enqueue_style('ds-wp-cpt-api-admin-ui','https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.0/themes/base/jquery-ui.css',false,"1.9.0",false);

      //allow media file uploads
      wp_enqueue_media();
      wp_enqueue_script('ds-wp-cpt-api-mediauploader', plugins_url('/js/ds-wp-cpt-file-uploader.js', __FILE__), array('jquery'), '1.3.5', '');

      // Load the JS
      wp_enqueue_script( 'ds-wp-cpt-api-admin', plugins_url( '/js/ds-wp-cpt-api-admin.js', __FILE__ ), array('jquery'), '1.3.8', true );
    } // end if( is_object($screen) && 'wpla_licenses' == $screen->post_type )
  }

  public function ds_wp_cpt_error_messages() {

    $this->wp_user_id = get_current_user_id();
    $this->current_post_id = get_the_ID();

    if($this->cpt_wp_error = get_transient("{$this->custom_post_type}_error_{$this->current_post_id}_{$this->wp_user_id}")) {

      // get the items from the WP_Error
      foreach($this->cpt_wp_error->error_data as $field_id => $error_message_items) {
        ?>
        <div class="error">
          <?php // see if there are multiple messages set
          foreach ($this->cpt_wp_error->errors[$field_id] as $field_message_error) { ?>
            <p><strong><?php echo $error_message_items['label']; ?></strong> <?php echo $field_message_error; ?></p>
        <?php } ?>
        </div>
        <?php
      } // end foreach($this->cpt_wp_error->error_data as $field_id => $error_message_items)
      delete_transient("{$this->custom_post_type}_error_{$this->current_post_id}_{$this->wp_user_id}");
    } // end if ( array_key_exists( $this->custom_post_type, $_SESSION ) )
  } //end function ds_wp_cpt_error_messages

  //Generic sections to add meta boxes to post types
  public function ds_wp_cpt_api_add_meta_boxes($post) {
    // first get the fields 
    $this->set_meta_box_fields($post->ID);

    foreach($this->meta_box_fields as $meta_box_section) {
      add_meta_box($meta_box_section['section_name'], $meta_box_section['title'], array( $this, 'ds_wp_cpt_api_standard_format_box'), $this->custom_post_type, $meta_box_section['context'], $meta_box_section['priority'], $meta_box_section);
    } //end foreach($meta_box_fields_to_add as $value)

  } //end function ds_wp_cpt_api_add_meta_boxes($meta_box)

  //Format meta boxes
  public function ds_wp_cpt_api_standard_format_box($post, $callback_fields) {
    // Use nonce for verification
    wp_nonce_field(basename(__FILE__), 'ds_wp_cpt_api_meta_box_nonce');

    echo '<div class="ds-wp-cpt-metabox-settings">';

    if(isset($callback_fields['args']['prepend_info'])) {
      echo '<div>' . $callback_fields['args']['prepend_info'] . '</div>';
    }
    if(isset($callback_fields['args']['info_blocks']) && is_array($callback_fields['args']['info_blocks'])) {
      foreach ($callback_fields['args']['info_blocks'] as $info_block) {
       echo '<div class="ds-wp-cpt-metabox-settings-info-block">' . $info_block . '</div>';
      }
    } // end if(isset($callback_fields['args']['info_blocks']) && is_array($callback_fields['args']['info_blocks']))
    if(isset($callback_fields['args']['fields']) && is_array($callback_fields['args']['fields'])) {
     foreach ($callback_fields['args']['fields'] as $field) {

        $field_default = isset($field['default']) && !empty($field['default']) ? $field['default'] : '';

        $saved_meta_value = null;
        // get the saved values if any
        $saved_meta_value = get_post_meta($post->ID, $field['id'], true);
        if($saved_meta_value != '' && $saved_meta_value != null) {
          if(is_array($saved_meta_value)) {
            $value_shown = $saved_meta_value;
          } else {
            $value_shown = esc_html($saved_meta_value);
          }
        } else {
          $value_shown = esc_html($field_default);
        } // end if($saved_meta_value != '' && $saved_meta_value != null) 
        
        // Read only flag
        if(isset($field['readonly']) && $field['readonly'] == 'true') {
          $readonly = 'readonly';
          $radio_readonly = 'disabled="disabled"';
        } else {
          $readonly = '';
          $radio_readonly = '';
        } // end if readonly 
        
        $field_desc = isset($field['desc']) && !empty($field['desc']) ? $field['desc'] : '';

        $field_class = isset($field['class']) && !empty($field['class']) ? $field['class'] : '';

        $field_required = isset($field['required']) && !empty($field['required']) ? $field['required'] : null;

        if(isset($field['allowedit']) && $field['allowedit'] == 'true') {
          $field_class .= ' noedit';
        }

        // Required field highlighting
        $field_messages = '';
        if( isset($field_required) && ($field_required == 'yes' || $field_required == 'true' || $field_required) && ($saved_meta_value == '') ){
          $field_class .= ' ds-wp-cpt-field-required';
          $field_messages = '<span class="ds-wp-cpt-required-message">* This field is required</span>';
        }

        $rowStart = '<div class="ds-wp-cpt-metabox-settings-row ' . $field['id'] . ' ' . $field_class . '">';

        // see if validation errors are set 
        if( isset($this->cpt_wp_error->error_data[$field['id']]) ) {
          $field_class .= ' ds-wp-cpt-field-validation-error';
          // set the readonly flag to false since they need to be able to fix their error 
          $readonly = '';
          if( isset($this->cpt_wp_error->error_data[$field['id']]['value']) ) {
            $field_default = $this->cpt_wp_error->error_data[$field['id']]['value'];
          }
        }

        $standardFieldLabel = $rowStart .
                '<div class="ds-wp-cpt-metabox-settings-label"><label for="'. $field['id'] .'">'. $field['label']. '</label><div>' . $field_messages . '</div></div>'.
                '<div class="ds-wp-cpt-metabox-settings-field">';
        $expandedFieldLabel = $rowStart .
                '<div class="ds-wp-cpt-metabox-settings-label ds-wp-cpt-metabox-settings-label-wide"><label for="'. $field['id'] .'">'. $field['label']. '</label><div>' . $field_messages . '</div></div>'.
                '<div class="ds-wp-cpt-metabox-settings-field">';
        $topFieldLabel = $rowStart .
                '<div class="ds-wp-cpt-metabox-settings-label ds-wp-cpt-metabox-settings-label-full"><label for="'.
                $callback_fields['id'] .'">'. $field['label']. '</label>' . $field_messages . '</div>'.
                '<div class="ds-wp-cpt-metabox-settings-field-full">';
        
        switch ($field['type']) {
          case 'info':
              echo $standardFieldLabel;
              echo '<div class="ds-wp-cpt-api-info ' . $field_class . ' ">' . $field_default . '</div>';
              break;
          case 'text':
              echo $standardFieldLabel;
              echo ' <input type="text" class="' . $field_class . '" name="'. $field['id']. '" id="'. $field['id'] .'" value="'. $value_shown . '" size="30" style="width:100%" ' . $readonly . '/>';
              break;
          case 'removable_display_array':
              echo $standardFieldLabel;
              echo '<div class="ds-cpt-removable-array-container">';
              if(is_array($value_shown)) {
                $meta_array_value_counter = 1;
                foreach($value_shown as $meta_array_value) {
                  echo '<div class="ds-cpt-removable-array-value" name="'. $field['id'] . '" id="'. $field['id'] .'-' . $meta_array_value_counter . '">' .  $meta_array_value;
                 echo '<input type="hidden" name="'. $field['id'] . '[]" id="'. $field['id'] .'-' . $meta_array_value_counter . '" value="'. $meta_array_value . '" / >';
                  echo '</div>';
                  $meta_array_value_counter++;
                } //end foreach($saved_meta_value as $array_value)
              }//end if(is_array($saved_meta_value)) {
              echo '</div>';
              break;
          case 'text_small':
            echo $standardFieldLabel;
            echo ' <input type="text" class="' . $field_class . '" name="'. $field['id']. '" id="'. $callback_fields['id'] .'" value="'. $value_shown . '" size="30" style="width:100%" ' . $readonly . '/>';
          break;

          case 'random_text':

            $random_string_length = isset($field['random_string_length']) && $field['random_string_length'] !== "" ? $field['random_string_length'] : '10';
            $random_uppercase_allowed = isset($field['random_uppercase']) && $field['random_uppercase'] !== "" ? $field['random_uppercase'] : 'yes';
            $random_string = $this->ds_wp_cpt_random_string($random_string_length, $random_uppercase_allowed);

            $value_shown = ($saved_meta_value ? $saved_meta_value : $random_string);

            echo $standardFieldLabel;
            echo ' <input type="text" class="' . $field_class . '" name="'. $field['id']. '" id="'. $callback_fields['id'] .'" value="'. $value_shown . '" size="30" style="width:100%" ' . $readonly . '/>';
          break;

          case 'number':
            echo $standardFieldLabel;
            $num_step_amount = isset($field['step']) && $field['step'] !== "" ? $field['step'] : '1';
            $num_min_amount = isset($field['min']) && $field['min'] !== "" ? $field['min'] : '0';
            $num_max_amount = isset($field['max']) && $field['max'] !== "" ? $field['max'] : '';
            echo ' <input type="number" class="' . $field_class . '" name="'. $field['id']. '" id="'. $field['id'] .'" value="'. $value_shown . '" step="' . $num_step_amount . '" min="' . $num_min_amount . '" max="' . $num_max_amount. '" size="30" style="width:100%" ' . $readonly . '/>';
          break;

          case 'textarea':
              echo $standardFieldLabel;
              echo '<textarea name="'. $field['id']. '" id="'. $field['id']. '" cols="60" rows="4" style="width:97%" ' . $readonly . '>'. $value_shown . '</textarea>';
              break;
          case 'select':
              echo $standardFieldLabel;
              echo '<select name="'. $field['id'] . '" id="'. $field['id'] . '" class="'. $field_class . '">';
              foreach ($field['options'] as $key => $option) {
                echo '<option value="' . $key . '"'. ( $value_shown == $key ? ' selected="selected"' : '' ) . '>'. $option . '</option>';
              }
              echo '</select>';

              break;
          case 'radio':
              echo $standardFieldLabel;
              //Set a counter for how many items there are
              //If this is the first item, we'll check it in case there
              //are no items actually checked
              $radioCounter = 1;

              //Get the selected or default value, if any
              $checked_value = $value_shown;

              echo '<div class="ds-wp-cpt-radio">';
              foreach ($field['options'] as $radioKey => $option) {
                echo '<input type="radio" value="'.$radioKey.'" class="'. $field_class . '" name="'.$field['id'].'" id="' . $field['id'] . '-' . $radioKey . '"',
                $checked_value == $radioKey || $radioCounter == 1 ? ' checked="checked"' : '',' ' . $radio_readonly . '/>
                <label for="' . $field['id'] . '-' . $radioKey .'">'.$option.'</label> &nbsp;&nbsp;';
                //increase the radioCounter
                $radioCounter++;
              }

              echo '</div>';
              break;
          case 'radio_on_off':
              echo $standardFieldLabel;

              //Get the selected or default value, if any
              $checked_value = $value_shown;

              // only two options are allowed here
        			$option_counter = 0;
        			echo '<div class="ds-switch">';

        			foreach ($field['options'] as $option_value => $option_text) {
        				// break if more than two options
        				if(++$option_counter > 2) break;

        				$checked = ' ';
        				// if (get_option($settings['id']) == $option_value) {
        				if ($checked_value == $option_value) {
        					$checked = ' checked="checked" ';
        				}
        				else if ($checked_value === FALSE && $settings['value'] == $option_value){
        					$checked = ' checked="checked" ';
        				}
        				else {
        					$checked = ' ';
        				}

        				echo '<input type="radio" class="ds-switch-input" name="' . $field['id']. '" id="' . $field['id'] . '_' . $option_value . '" value="' . $option_value . '" ' . $checked . '/>';
        				echo '<label class="ds-switch-label ds-switch-label-' . $option_counter . '" for="' . $field['id'] . '_' . $option_value . '">' . $option_text . '</label>';
        				// } // end for($option_counter = 1; $option_counter <=2; $option_counter++)
        			}// end foreach ($settings['options'] as $option_value => $option_text)
        			echo '<span class="ds-switch-selection">';
        			echo '</div>';

              break;
          case 'checkbox':
              echo $standardFieldLabel;
              echo '<div class="ds-wp-cpt-check">';
              foreach ($field['options'] as $checkKey => $option) {
                echo '<input type="checkbox" value="'.$option.'" name="'.$field['id'].'[]" id="' . $field['id'] . '_' . $checkKey . '"',$value_shown && in_array($option, $value_shown) ? ' checked="checked"' : '',' ' . $radio_readonly . '/>
                <label for="' . $field['id'] . '_' . $checkKey . '">'.$option.'</label> &nbsp;&nbsp;';
              }

              echo '</div>';
              break;
          case 'datepicker':
              if(isset($field['readonly']) && $field['readonly'] == 'true') {
                $readonly = 'readonly';
              } else {
                $readonly = '';
              }
              echo $standardFieldLabel;
              echo ' <input class="ds-cpt-datepicker" autocomplete="off" type="text" name="'. $field['id']. '" id="'. $field['id'] .'" value="'. $value_shown . '" size="30" style="width:100%" ' . $readonly . '/>';
              break;
          case 'texteditor':
              echo $topFieldLabel;
              $textarea_rows = 10;
              if(isset($field['textarea_rows']) && $field['textarea_rows'] > 0) {
                $textarea_rows = $field['textarea_rows'];
              } // end if
              wp_editor( $value_shown, $field['id'], array(
                'wpautop'       => true,
                'media_buttons' => false,
                'textarea_name' => $field['id'],
                'textarea_rows' => $textarea_rows,
                'teeny'         => true
              ));
              break;
          case 'field_with_select':
              $optional_args = '';

              if(isset($field['readonly']) && $field['readonly'] == 'true') {
                $readonly = 'readonly';
              } else {
                $readonly = '';
              }

              $field_type = isset($field['field_type']) ? $field['field_type'] : 'text';

              if($field_type == 'number') {
                $number_min = isset($field['min']) ? $field['min'] : '';
                $number_max = isset($field['max']) ? $field['max'] : '';
                $optional_args = 'min="' . $number_min . '" max="' . $number_max . '"';
              } // end if($field_type == 'number')

              // handle defaults for our select id and text id
              if(isset($value_shown[$field['textid']]) && !empty($value_shown[$field['textid']])) {
                $textid_value = $value_shown[$field['textid']];
              } else if(isset($field['textdefault'])) {
                $textid_value = $field['textdefault'];
              } else {
                $textid_value = '';
              } // end if(isset($value_shown[$field['textid']]) && !empty($value_shown[$field['textid']]))

              // handle defaults for our select id and text id
              if(isset($value_shown[$field['selectid']]) && !empty($value_shown[$field['selectid']])) {
                $selectid_value = $value_shown[$field['selectid']];
              } else if(isset($field['selectdefault'])) {
                $selectid_value = $field['selectdefault'];
              } else {
                $selectid_value = '';
              } // end if(isset($value_shown[$field['selectid']]) && !empty($value_shown[$field['selectid']]))

              echo $standardFieldLabel;
              echo '<input type="' . $field_type . '" name="'. $field['id'] . '[' . $field['textid'] . '] " id="'. $field['id'] . '[' . $field['textid'] . ']" value="'.
              $textid_value . '" size="30" ' . $optional_args . ' style="width:100px; margin-right: 10px; vertical-align:bottom;" / ' . $readonly . '>';

              echo '<select name="'. $field['id'] . '[' . $field['selectid'] . '] " id="'. $field['id'] . '[' . $field['selectid'] . ']" style="vertical-align="bottom">';
              foreach ($field['selectoptions'] as $key => $option) {
                echo '<option value="' . $key . '"' . ( $selectid_value == $key ? ' selected="selected"' : '' ) . '>'. $option . '</option>';
              }
              echo '</select>';
            break;
          case 'post_title_select':

              //see if there are other posts with the same post title
              $ds_wp_cpt_api_post_type_query = new \WP_Query(
                  array(
                    'post_type' => $field['post_type'],
                    'posts_per_page' => -1
                  )
                );
              $ds_wp_cpt_api_posts_array = $ds_wp_cpt_api_post_type_query->posts;
              $ds_wp_cpt_api_post_title_array = wp_list_pluck($ds_wp_cpt_api_posts_array, 'post_title', 'ID');

              // get currently selected item 
              echo $standardFieldLabel;

              echo '<select class="ds-wp-cpt-post-title-select" id="' . $field['id'] . '" name="' . $field['id']. '">';

              foreach ($ds_wp_cpt_api_post_title_array as $ds_wp_cpt_api_post_title_ID => $ds_wp_cpt_api_post_title) {
                if(isset($field['fields_shown']) && is_array($field['fields_shown'])) {
                  // Build the array string:
                  $ds_wp_cpt_api_post_title_value_shown = '';
                  foreach($field['fields_shown'] as $key => $option_name) {
                    if($key == 'title') {
                      $ds_wp_cpt_api_post_title_value_shown .= $option_name . $ds_wp_cpt_api_post_title;
                    } else if ($key == 'id') {
                      $ds_wp_cpt_api_post_title_value_shown .= $option_name . $ds_wp_cpt_api_post_title_ID;
                    } else {
                      $ds_wp_cpt_api_post_title_value_shown .= $option_name . ' ';
                      $ds_wp_cpt_api_post_title_value_shown .= get_post_meta($ds_wp_cpt_api_post_title_ID, $key, true) . ' ';
                    }
                  }

                  echo '<option ' . selected( $value_shown, $ds_wp_cpt_api_post_title_ID ) . ' value="' . $ds_wp_cpt_api_post_title_ID . '">' . $ds_wp_cpt_api_post_title_value_shown . '</option>';
                } else {
                  echo '<option ' . selected( $value_shown, $ds_wp_cpt_api_post_title_ID ) . ' value="' . $ds_wp_cpt_api_post_title_ID . '">' . $ds_wp_cpt_api_post_title . '  (ID: ' . $ds_wp_cpt_api_post_title_ID . ')</option>';
                }

              } //end foreach ($ds_wp_cpt_api_post_title_array as $ds_wp_cpt_api_post_title_ID => $ds_wp_cpt_api_post_title)

              echo '</select>';
              if($value_shown != '') {
                echo '<a class="ds-wp-cpt-post-title-select" id="' . $field['id']  . '_link" href="/wp-admin/post.php?post=' . $value_shown . '&action=edit" target="_blank">Show Selection</a>';
              }
            break;
          case 'pdfattachment':
              echo $standardFieldLabel;
              if(!empty($value_shown['url'])):
                $path = parse_url($value_shown['url'], PHP_URL_PATH);
                $pathFragments = explode('/', $value_shown['url']);
                $end = end($pathFragments);
                echo '<a href="'. $value_shown['url'] .'" target="_blank">' . $end . '</a>';
              endif;
              echo ' <input type="file" name="'. $field['id']. '" id="'. $field['id'] .'" size="30" style="width:100%" />';
            break;
          case 'media':
              global $post;

              // See if there's a media id already saved as post meta
              $ds_wp_cpt_attachment_media_id = get_post_meta( $post->ID, $field['id'], true );

              // Get the image src
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

              echo $standardFieldLabel;
              echo '<div class="ds-wp-cpt-uploader">';
              echo '<div class="ds-wp-cpt-uploader-removable ' . $removeable_class . '">';

              echo '<input name="' . $field['id'] . '" id="' . $field['id'] . '" class="ds-wp-cpt-uploader-value" type="hidden"  value="'. $value_shown . '" />';
              echo '<div id="' . $field['id'] . '-img-container" class="ds-wp-cpt-uploader-image-container">';
              if ( $ds_wp_cpt_attachment_media_img_src != '' ) {
                echo '<img src="' . $ds_wp_cpt_attachment_media_img_src[0] . '" alt="" style="max-width:100%;" />';
              } // end if ( $ds_wp_cpt_attachment_media_img_src ) 
              echo '</div>';              
              echo '<p id="' . $field['id'] . '-file-name" class="ds-wp-cpt-file-name">' . $ds_wp_cpt_attachment_media_src . '</p>';
              echo '</div>';
              echo '<input id="' . $field['id'] .  '_button" class="ds-wp-cpt-upload-button button" name="' . $field['id'] . '_button" type="button" value="Upload / Change" />';
              echo '</div>';
            break;
          case 'image':

              global $post;

              // Get WordPress' media upload URL
              $upload_link = esc_url( get_upload_iframe_src( 'image', $post->ID ) );

              // See if there's a media id already saved as post meta
              $ds_wp_cpt_attachment_img_id = get_post_meta( $post->ID, $field['id'], true );

              // Get the image src
              $ds_wp_cpt_attachment_img_src = wp_get_attachment_image_src( $ds_wp_cpt_attachment_img_id, 'full' );

              // For convenience, see if the array is valid
              $ds_wp_cpt_attachment_have_img = is_array( $ds_wp_cpt_attachment_img_src );

              // add a class to the removable div to show hover effects 
              $removeable_class = '';
              if( $ds_wp_cpt_attachment_have_img ) $removeable_class = 'has-image';
              
              echo $standardFieldLabel;
              echo '<div class="ds-wp-cpt-image-uploader">';
              echo '<div class="ds-wp-cpt-image-uploader-removable ' . $removeable_class . '">';
              echo '<div id="' . $field['id'] . '-img-container" class="ds-wp-cpt-image-uploader-image-container">';
              if ( $ds_wp_cpt_attachment_have_img ) {
                echo '<img src="' . $ds_wp_cpt_attachment_img_src[0] . '" alt="" style="max-width:100%;" />';
              } // end if ( $ds_wp_cpt_attachment_have_img ) 
              echo '</div>';

              echo '<!-- A hidden input to set and post the chosen image id -->
                    <input name="' . $field['id'] . '" id="' . $field['id'] . '"  class="ds-wp-cpt-image-uploader-value" type="hidden"  value="'. $value_shown . '"/>';
              echo '</div>';
              echo '<input id="' . $field['id'] . '_button" class="ds-wp-cpt-upload-button button" name="' . $field['id'] . '_button" type="button" value="Upload / Change" />';

              echo '</div>';
            break;
          case 'gallery':

            global $post;

            // Get WordPress' media upload URL
            $upload_link = esc_url( get_upload_iframe_src( 'image', $post->ID ) );

            // See if there's a media id already saved as post meta
            $ds_wp_cpt_attachment_img_gallery_ids = get_post_meta( $post->ID, $field['id'], true );

            echo $standardFieldLabel;
            echo '<div class="ds-wp-cpt-image-gallery-uploader">';

            echo '<div id="' . $field['id'] . '-img-gallery-container" class="ds-wp-cpt-image-uploader-image-gallery-container">';

            $gallery_counter = 0;
            
            if(is_array($ds_wp_cpt_attachment_img_gallery_ids)) {
              
              foreach ($ds_wp_cpt_attachment_img_gallery_ids as $ds_gallery_image_id) {
                  // Get the image src
                  $ds_wp_cpt_attachment_img_gallery_src = wp_get_attachment_image_src( $ds_gallery_image_id, 'thumbnail' );
                  // For convenience, see if the array is valid
                  $ds_wp_cpt_attachment_have_img_gallery = is_array( $ds_wp_cpt_attachment_img_gallery_src );

                  // add a class to the removable div to show hover effects 
                  if ( $ds_wp_cpt_attachment_have_img_gallery ) {
                    echo '<div class="ds-wp-cpt-image-gallery-uploader-removable has-image">';
                      echo '<img src="' . $ds_wp_cpt_attachment_img_gallery_src[0] . '" alt="" />';
                      echo '<input name="' . $field['id'] . '[' . $gallery_counter . ']" id="' . $field['id'] . $gallery_counter . '"  class="ds-wp-cpt-image-gallery-uploader-value" type="hidden"  value="' . $ds_gallery_image_id . '"/>';
                    echo '</div>';

                  } // end if ( $ds_wp_cpt_attachment_have_img ) 
                  ++$gallery_counter;
              } // end foreach
            } // end if 

            echo '</div>';

            echo '<!-- A hidden input to track the image ids -->
                  <input name="' . $field['id'] . '-counter" id="' . $field['id'] . '-counter" type="hidden"  value="' . $gallery_counter . '"/>';

            echo '<input id="' . $field['id'] . '_button" class="ds-wp-cpt-upload-button button" name="' . $field['id'] . '_button" type="button" value="Upload" />';

            echo '</div>';
          break;
          default: 
            echo $topFieldLabel;
            echo 'Invalid type selected.';
        }

        if( isset($this->cpt_wp_error->error_data[$field['id']]) ) {
          echo '<div class="ds-wp-cpt-field-error">';
            foreach ($this->cpt_wp_error->errors[$field['id']] as $field_message_error) {
              echo '<p>ERROR: ' . $field_message_error . '</p>';
            }
          echo '</div>';
        } //end if( isset($this->cpt_wp_error->error_data[$field['id']]) ) {
        echo '<div class="ds-wp-cpt-field-desc">' . $field_desc . '</div>';
        echo '</div> <!--close ds-wp-cpt-metabox-settings-field-->';
        echo '</div><!--close ds-wp-cpt-metabox-settings-row-->';
      } //end foreach ($callback_fields['args'] as $field) 
    } // end if(isset($callback_fields['args']['fields']) && is_array($callback_fields['args']['fields'])) 

    echo '</div>';
    if(isset($callback_fields['args']['append_info'])) {
      echo '<div>' . $callback_fields['args']['append_info'] . '</div>';
    }
  }//end  function ds_wp_cpt_api_standard_format_box($post, $callback_fields)

  // checks if we are on the Add post screen or editing an existing post
  public function is_new_post() {

    // make sure this function exists first 
    if ( ! function_exists( 'get_current_screen' ) ) {
      return false;
    } else {
      // get the screen
      $screen = get_current_screen();
      if( is_object($screen) && $this->custom_post_type == $screen->post_type ) {
        if($screen->action == 'add') {
          return true;
        } else {
          return false;
        } // end if screen action
      } // end if screen post type
    } // end if ( ! function_exists( 'get_current_screen' ) )

  } // end function is_new_post

  public function ds_wp_cpt_api_save_data($post_id) {

    // Set the metabox fields
    $this->set_meta_box_fields($post_id);

    // get the current user id for setting validation errors
    $this->wp_user_id = \get_current_user_id();
    $this->current_post_id = $post_id;

    $meta_box_fields = $this->meta_box_fields;

    //Verify nonce
    if (!isset($_POST['ds_wp_cpt_api_meta_box_nonce']) || !wp_verify_nonce($_POST['ds_wp_cpt_api_meta_box_nonce'], basename(__FILE__))) {
        return;
    } else {
      //Check autosave
      if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
          return $post_id;
      }

      //Check permissions
      if (!current_user_can('edit_page', $post_id)) {
          return $post_id;
      }

      foreach($meta_box_fields as $meta_box_values) {

        if(isset($meta_box_values['fields']) && is_array($meta_box_values['fields'])) {

          foreach($meta_box_values['fields'] as $field){

            // get the validation type. If not set, send the type of field
            $validation_type = isset($field['validation']) && !empty($field['validation']) ? $field['validation'] : $field['type'];

            $existing_value = get_post_meta($post_id, $field['id'], true);
            
            // check if the POST actually contains the field we're going to check against 
            $submitted_value = isset($_POST[$field['id']]) && !empty($_POST[$field['id']]) ? $_POST[$field['id']] : null;

            $sanitized_value = '';

            switch($validation_type) {

              //check if this is a file upload
              case 'pdfattachment':
                //Check if the $_FILES array is filled
                if(!$_FILES[$field['id']]['error'] == 4) {

                  $supported_types = array('application/pdf');
                  $arr_file_type = wp_check_filetype(basename($_FILES[$field['id']]['label']));
                  $uploaded_type = $arr_file_type['type'];
                  $upload = wp_upload_bits($_FILES[$field['id']]['label'], null, file_get_contents($_FILES[$field['id']]['tmp_name']));
                  if(in_array($uploaded_type, $supported_types)) {
                    $upload = wp_upload_bits($_FILES[$field['id']]['label'], null, file_get_contents($_FILES[$field['id']]['tmp_name']));
                    if(isset($upload['error']) && $upload['error'] != 0) {
                      $this->create_wp_error($post_id, $field['id'], $label, 'There was an error uploading your file. The error is: ' . $upload['error']);
                    } else {
                      $sanitized_value = $upload;
                      // update_post_meta($post_id, $field['id'], $upload);
                    }
                  } else {
                    $this->create_wp_error($post_id, $field['id'], $label, 'The file type that you\'ve uploaded is not a PDF.');
                  } // end if(in_array($uploaded_type, $supported_types))
                } //end if(!$_FILES[$field['id']]['error'] == 4)
                break;
              case 'number':
                $sanitized_value = $this->validate_number($_POST[$field['id']],
                $field['id'], $field['label']);
                break;
              case 'texteditor':
                $sanitized_value = $submitted_value;
                break;
              case 'api_key':
                $min = isset(	$field['validation_min'] ) ? $field['validation_min'] : 5;
                $max = isset(	$field['validation_max'] ) ? $field['validation_max'] : 20;
                $sanitized_value = $this->validate_api_key($_POST[$field['id']],
                $field['id'], $field['label'], $min, $max);
                break;
              default:
                //Do a regular update
                //sanitize
                if(is_array($submitted_value)) {
                  $sanitized_value = array();
                  foreach ($submitted_value as $array_key => $array_item) {
                    $sanitized_value[$array_key] = sanitize_text_field($array_item);
                  }
                } else {
                  $sanitized_value = sanitize_text_field($submitted_value);
                }
                break;

            } //end switch($validation_type)

            $unique_key = isset($field['unique_key']) && $field['unique_key'] !== "" ? $field['unique_key'] : 'false';
            if($unique_key == 'true') {
              $sanitized_value = $this->validate_unique_key($sanitized_value,
              $field['id'], $field['label']);
            }
            //save the sanitized value or retain the existing one
            if (isset($sanitized_value) && $sanitized_value != $existing_value) {
                update_post_meta($post_id, $field['id'], $sanitized_value);
            } elseif ('' == $sanitized_value && $existing_value) {
                delete_post_meta($post_id, $field['id'], $existing_value);
            } // end if ($new_value && $new_value != $existing_value)
          } //end foreach($meta_box_sections as $meta_box_values)
        } // end if(isset($meta_box_values['fields']) && is_array($meta_box_values['fields']))
      } // end foreach($meta_box_fields as $post => $meta_box_sections)
    } //end if (!isset($_POST['ds_wp_cpt_api_meta_box_nonce'])
  } // end function ds_wp_cpt_api_save_data
  public function validate_unique_key($field_value, $field_id, $label) {
    //see if there are other posts with the same post title
    $cpt_query = new \WP_Query(
        array(
          'post_type' => $this->custom_post_type,
          'posts_per_page' => -1
        )
      );
    $cpt_post_array = $cpt_query->posts;

    $cpt_post_id_array = wp_list_pluck($cpt_post_array, 'ID');
    $key_match = false;
    // check if there's already a post with the same key
    foreach ($cpt_post_id_array as $cpt_post_id) {
      // do not process if the cpt_post_id is the same as our current post idea
      if($this->current_post_id != $cpt_post_id) {
        //get the current meta value
        $cpt_unique_key_value = get_post_meta($cpt_post_id, $field_id, true);
        if($cpt_unique_key_value == $field_value) {
          $key_match = true;
          break;
        }
      } //end if($current_post_id != $cpt_post_id)
    } //end foreach
    if($key_match) {
      $this->create_wp_error($field_id, $field_value, $label, 'This key may not be the same as any other keys and cannot be blank');
      return;
    } else {
      return sanitize_text_field($field_value);
    }
  } // end function validate_unique_key
  //no spaces, no special chars, underscores and dashes allowed, limited to 20
  //total characters, must have a minimum of six characters
  public function validate_api_key($field_value, $field_id, $label, $min=5, $max=20) {
    if (preg_match('#^[-_A-z0-9]{' . $min . ',' . $max . '}$#', $field_value)) {
      return sanitize_text_field($field_value);
    } else {
      $this->create_wp_error($field_id, $field_value, $label, 'may only contain upper and lower case, underscores and dashes, minimum of ' . $min . ' and maximum of ' . $max . ' characters');
      return;
    } // end if (preg_match('#^[a-zA-Z0-9_-]{6,20}$D#', $input))
  } //end function validate_api_key
  public function validate_number($field_value, $field_id, $label) {
    // check if it's numeric or just blank
    if((is_numeric($field_value) && $field_value >= 0) || $field_value == ''){
      return sanitize_text_field($field_value);
    } else {
      $this->create_wp_error($field_id, $field_value, $label, 'make sure this field only contains a number');
    }
  } // end function validate_number

  public function create_wp_error($field_id, $field_value, $label, $message) {
    if(!is_wp_error($this->cpt_wp_error)) {
      $this->cpt_wp_error = new \WP_Error();
    }
    //make sure the bad value is sanitized before we show it to the user
    $field_value = sanitize_text_field($field_value);
    $error_data = array(
      'label' => $label,
      'message' => $message,
      'value' => $field_value
    );
    $this->cpt_wp_error->add($field_id, $message, $error_data);
    //create a transient with the error
    set_transient("{$this->custom_post_type}_error_{$this->current_post_id}_{$this->wp_user_id}", $this->cpt_wp_error, 120);
	} // end function create_settings_error

  public function ds_wp_cpt_random_string($length = 10, $uppercase = 'yes') {
    if($uppercase == 'yes') {
      $x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    } else if ($uppercase == 'no') {
      $x = '0123456789abcdefghijklmnopqrstuvwxyz';
    }
    return substr(str_shuffle(str_repeat($x, ceil($length/strlen($x)) )),1,$length);
  }

}} // END CLASS
