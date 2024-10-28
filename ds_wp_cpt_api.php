<?php
// GitHub: N/A
// Version 1.6.4
// Author: Steve Talley
// Organization: Dusty Sun
// Author URL: https://dustysun.com/

namespace Dusty_Sun\WP_CPT_API\v1_4;
require_once( dirname( __FILE__ ) . '/classes/class-fields.php');
require_once( dirname( __FILE__ ) . '/classes/class-repeater_fields.php');

// This parent class cannot do anything on its own - must be extended by a child class
if(!class_exists('Dusty_Sun\WP_CPT_API\v1_4\CPTBuilder'))  { class CPTBuilder {

  // $meta_box_fields must be set by a child by calling set_meta_box_fields
  private $meta_box_fields;

  // $custom_post_type must be set by a child by calling set_custom_post_type
  protected $custom_post_type;

  // validation errors
  private $cpt_wp_error;

  // used for validations
  protected $current_post_id;
  protected $wp_user_id;

  public $text_domain = 'dustysun_cpt';

  public function __construct(){
    add_action('admin_enqueue_scripts', array($this, 'register_ds_wp_cpt_api_admin_scripts'));

    // admin notices / errors
    add_action('admin_notices', array($this, 'ds_wp_cpt_error_messages'));

    // Add the meta box fields
    add_action('add_meta_boxes_' . $this->custom_post_type, array($this,'ds_wp_cpt_api_add_meta_boxes'), 1, 2);

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
      
      // WooCommerce scripts if a WooCommerce block is used
      if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {

        wp_register_style( 'woocommerce-admin', WC()->plugin_url() . '/assets/css/admin.css' );
        wp_enqueue_style( 'woocommerce-admin' );

        wp_register_script( 'wc-enhanced-select', WC()->plugin_url() . '/assets/js/admin/wc-enhanced-select.min.js', array( 'jquery', 'selectWoo' ) );
        wp_enqueue_script( 'wc-enhanced-select' );
		  } // end if is_plugin_active


      // load the css
      wp_enqueue_style('ds-wp-cpt-api', $this->get_file_url('/css/ds-wp-cpt-api-admin.css'));

      // Load the datepicker script (pre-registered in WordPress).
      wp_enqueue_script( 'jquery-ui-datepicker' );

      //jQuery UI theme css file
      wp_enqueue_style('ds-wp-cpt-api-admin-ui','https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.0/themes/base/jquery-ui.css',false,"1.9.0",false);

      //allow media file uploads
      wp_enqueue_media();
      wp_enqueue_script('ds-wp-cpt-api-mediauploader', $this->get_file_url('/js/ds-wp-cpt-file-uploader.js'), array('jquery'), '1.3.5', '');

      // Load the JS
      wp_enqueue_script( 'ds-wp-cpt-api-admin', $this->get_file_url('/js/ds-wp-cpt-api-admin.js'), array('jquery'), '1.3.8', true );
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

    if(!empty($this->meta_box_fields)) {
      foreach($this->meta_box_fields as $meta_box_section) {
        add_meta_box($meta_box_section['section_name'], $meta_box_section['title'], array( $this, 'ds_wp_cpt_api_standard_format_box'), $this->custom_post_type, $meta_box_section['context'], $meta_box_section['priority'], $meta_box_section);
      } //end foreach($meta_box_fields_to_add as $value)
    }

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

        // see if a custom saved value is passed -- this happens if the parent field definition
        // is pulling in the saved value in a custom way
        if(isset($field['custom_saved_value'])) {
          $value_shown = $field['custom_saved_value'];
        } else {
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
          } // end if 
        }
        
        // Check for a missing label 
        $field['label'] = isset($field['label']) && !empty($field['label']) ? $field['label'] : '';

        // Read only flag
        if(isset($field['readonly']) && $field['readonly'] == 'true') {
          $readonly = 'readonly';
          $radio_readonly = 'disabled="disabled"';
          $readonly_bool = true;
        } else {
          $readonly = '';
          $radio_readonly = '';
          $readonly_bool = false;
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

        $rowStart = '<div class="ds-wp-cpt-metabox-settings-row ' . $field['id'] . ' ' . $field_class . ' ' . $field['type'] . '">';

        // see if validation errors are set 
        if( isset($this->cpt_wp_error->error_data[$field['id']]) ) {
          $field_class .= ' ds-wp-cpt-field-validation-error';
          // set the readonly flag to false since they need to be able to fix their error 
          $readonly = '';
          if( isset($this->cpt_wp_error->error_data[$field['id']]['value']) ) {
            $field_default = $this->cpt_wp_error->error_data[$field['id']]['value'];
          }
        }
        $help_text = isset($field['help_text']) ? $field['help_text'] : '';

        $standardFieldLabel = $rowStart .
                '<div class="ds-wp-cpt-metabox-settings-label"><label for="'. $field['id'] .'">'. $field['label']. '</label><div>' . $field_messages . '</div><div class="ds-wp-cpt-metabox-settings-help-text">' . $help_text . '</div></div>'.
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
              echo CPTBuilder\CPT_InputFields::render_input_text($field['id'], $field_class, $value_shown, $readonly_bool);
              // echo ' <input type="text" class="' . $field_class . '" name="'. $field['id']. '" id="'. $field['id'] .'" value="'. $value_shown . '" size="30" style="width:100%" ' . $readonly . '/>';
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
              $field['rows'] = isset($field['rows']) && $field['rows'] !== "" ? $field['rows'] : '4';
              echo '<textarea name="'. $field['id']. '" id="'. $field['id']. '" cols="60" rows="' . $field['rows'] . '" ' . $readonly . '>'. $value_shown . '</textarea>';
              break;
          case 'select':
              echo $standardFieldLabel;
              $field_name = $field['id'];
              // handle a multiple selection
              $multiple = isset($field['multiple']) && !empty($field['multiple']) ? $field['multiple'] : false;
              $multiple_key = '';
              if($multiple) {
                $field_name = $field['id'] . '[]';
                $multiple_key = 'multiple';
              }
              echo '<select name="'. $field_name . '" id="'. $field['id'] . '" class="'. $field_class . '" ' . $multiple_key . '>';

              $dynamic_options = isset($field['dynamic_options']) && !empty($field['dynamic_options']) ? $field['dynamic_options'] : false;

              $option_fields = array();

              if(isset($field['options'])) { 
                $option_fields = $field['options'];
              }
              // see if any values have been set
              if($dynamic_options && $value_shown != '') {
                if(is_array($value_shown)) {
                  foreach($value_shown as $value) {
                    $option_fields[$value] = $value;
                  }
                } else {
                  $option_fields[$value_shown] = $value_shown;
                }
              }

              if(!empty($option_fields)) {
                foreach ($option_fields as $key => $option) {

                  $selected_option = '';
                  if(is_array($value_shown)) {
                    if(in_array($key, $value_shown)) {
                      $selected_option =  'selected="selected"';
                    } 
                  } else {
                    if($key == $value_shown) {
                      $selected_option =  'selected="selected"';
                    }
                  }
                  echo '<option value="' . $key . '"'. $selected_option . '>'. $option . '</option>';
                }
              }
              echo '</select>';

              break;
          case 'radio':
              echo $standardFieldLabel;
              
              echo CPTBuilder\CPT_InputFields::render_radio_input($field['id'], $field['options'], $field_class, $value_shown, $readonly_bool);

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
        				else if ($checked_value === FALSE && $field['value'] == $option_value){
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
                
              echo CPTBuilder\CPT_InputFields::render_checkbox_input($field['id'], $field['options'], $field_class, $value_shown, $readonly_bool, $field);

              // echo '<div class="ds-wp-cpt-check">';
              // foreach ($field['options'] as $checkKey => $option) {
              //   echo '<input type="checkbox" value="'.$checkKey.'" name="'.$field['id'].'[]" id="' . $field['id'] . '_' . $checkKey . '"',$value_shown && in_array($checkKey, $value_shown) ? ' checked="checked"' : '',' ' . $radio_readonly . '/>
              //   <label for="' . $field['id'] . '_' . $checkKey . '">'.$option.'</label> &nbsp;&nbsp;';
              // }

              // echo '</div>';
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
              wp_editor( html_entity_decode($value_shown), $field['id'], array(
                'wpautop'       => isset($field['wpautop']) ? $field['wpautop'] : true,
                'media_buttons' => isset($field['media_buttons']) ? $field['media_buttons'] : false,
                'textarea_name' => $field['id'],
                'textarea_rows' => $textarea_rows,
                'teeny'         => isset($field['teeny']) ? $field['teeny'] : false,
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

              if(!isset($field['orderby']) || $field['orderby'] == '') {
                $field['orderby'] = 'title';
              }
              if(!isset($field['order']) || $field['order'] == '') {
                $field['order'] = 'ASC';
              }
              //see if there are other posts with the same post title
              $ds_wp_cpt_api_post_type_query = new \WP_Query(
                  array(
                    'post_type' => $field['post_type'],
                    'orderby' => $field['orderby'],
                    'order' => $field['order'],
                    'posts_per_page' => -1,
                    'post_status' => 'publish'
                  )
                );
              $ds_wp_cpt_api_posts_array = $ds_wp_cpt_api_post_type_query->posts;
              $ds_wp_cpt_api_post_title_array = wp_list_pluck($ds_wp_cpt_api_posts_array, 'post_title', 'ID');

              // get currently selected item 
              echo $standardFieldLabel;

              echo '<select class="ds-wp-cpt-post-title-select" id="' . $field['id'] . '" name="' . $field['id']. '">';
              echo '<option value=""></option>';
              
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

              echo $standardFieldLabel;
              $mime_type = isset($field['mime_type']) ? $field['mime_type'] : 'all';

              echo CPTBuilder\CPT_InputFields::render_media_input($field['id'], $value_shown, $mime_type);

            break;

          case 'media_enhanced':

              // global $post;

              $mime_type = isset($field['mime_type']) ? $field['mime_type'] : 'all';

              // Check for repeater 
              $is_repeater = false;
              $field_id_array = '';
              if(isset($field['repeater']) ? $field['repeater'] : false) {
                $is_repeater = true;
              }

              // the repeater saved values are already an array so don't modify them
              if($is_repeater) {
                $values_array = $value_shown;
              } else {
                // add any existing values to an array
                $values_array = array($value_shown);
              }
              // if nothing was set we need to create the array so that there are no errors
              if(!(is_array($values_array))) {
                $values_array = array('');
              }
              echo $standardFieldLabel;

              // Check for repeater 

              if($is_repeater) {
                echo '<div class="ds-wp-cpt-repeater" data-id="' . $field['id'] . '">';
              }

              $counter = 0;
              foreach($values_array as $value_item){
                if($is_repeater) {
                  $field_id_array = '[' . $counter . ']';
                } else {
                  $field_id_array = '';
                }

                if(!is_array($value_item)){
                  $media_id = $value_item;
                } else {
                  $media_id = isset($value_item['media_id']) ? $value_item['media_id']: '';
                }
                // Get the media src
                $ds_wp_cpt_attachment_media_src = wp_get_attachment_url( $media_id, 'full' );

                $ds_wp_cpt_attachment_media_img_src = '';
                if($ds_wp_cpt_attachment_media_src != '') {
                  // Get the image src
                  $ds_wp_cpt_attachment_media_img_src = wp_get_attachment_image_src( $media_id, 'thumbnail', true );
                } // end if

                // For convenience, see if the array is valid
                $ds_wp_cpt_attachment_media_have_img = is_array( $ds_wp_cpt_attachment_media_img_src );


                // add a class to the removable div to show hover effects 
                $removeable_class = '';
                if( $ds_wp_cpt_attachment_media_have_img ) $removeable_class = 'has-media';
                
                $media_flex_order = isset($field['order']) ? $field['order'] : '1';
                if($is_repeater) {
                  echo '<div class="ds-wp-cpt-repeater-item"><button class="ds-wp-cpt-repeater-item-remove">Remove Item</button>';
                }
                echo '<div class="ds-wp-cpt-uploader ds-wp-cpt-media-uploader" style="order: ' . $media_flex_order . ';">';
                echo '<div class="ds-wp-cpt-uploader-removable ' . $removeable_class . '">';
                echo '<div class="ds-wp-cpt-remove"><span class="fa-stack"><i class="fa fa-circle fa-stack-1x icon-background"></i><i class="fa fa-times-circle fa-stack-1x"></i></span></div>';
                echo '<div class="ds-wp-cpt-image-uploader-image-container">';

                if ( $ds_wp_cpt_attachment_media_img_src != '' ) {
                  echo '<img src="' . $ds_wp_cpt_attachment_media_img_src[0] . '" loading="lazy" alt="" style="max-width:100%;" />';
                } // end if ( $ds_wp_cpt_attachment_media_img_src ) 
                echo '</div>';
                echo '<p class="ds-wp-cpt-file-name">' . $ds_wp_cpt_attachment_media_src . '</p>';
                echo '<!-- A hidden input to set and post the chosen media id -->
                      <input name="' . $field['id'] . $field_id_array . '[media_id]"  class="ds-wp-cpt-uploader-value" type="hidden"  value="'. $media_id . '"/>';
                echo '<button class="ds-wp-cpt-upload-button button" data-mime_type="' . $mime_type . '" /><span class="no-image">Upload Media</span><span class="has-image">Change Media</span></button>';
                echo '</div>';
                echo '</div>';

                // TEXT FIELD PORTION
                if(isset($field['options'])) {

                  if(isset($field['options']['text_field'])) {
                    $text_value = isset($value_shown['text']) ? $value_shown['text'] : '';
                    $text_flex_order = isset($field['options']['text_field']['order']) ? $field['options']['text_field']['order'] : '1';

                    echo '<div class="ds-wp-cpt-media-options-text_field" style="order: ' .$text_flex_order . ';">';

                    if(isset($field['options']['text_field']['placeholder'])) {
                      $placeholder = $field['options']['text_field']['placeholder']; 
                    } else {
                      $placeholder = '';
                    }
                    $text_input = '<input type="text" class="' . $field_class . '" name="'. $field['id'] . $field_id_array . '[text]" id="'. $field['id'] . $field_id_array .'[text]" placeholder="' . $placeholder . '" value="'. $text_value . '" size="30" style="width:100%" />';
      
                    if(isset($field['options']['text_field']['label'])) {
                      echo '<label for="' . $field['id'] . $field_id_array . '[text]">' . $field['options']['text_field']['label'] . $text_input . '</label>';
                    } else {
                      echo $text_input;
                    }
      
                    echo '</div>';
                  }
                  if(isset($field['options']['number_field'])) {
                    $number_value = isset($value_item['number']) ? $value_item['number'] : '';
                    $number_flex_order = isset($field['options']['number_field']['order']) ? $field['options']['number_field']['order'] : '2';
                    $number_min = isset($field['options']['number_field']['min']) ? $field['options']['number_field']['min'] : '1';

                    echo '<div class="ds-wp-cpt-media-options-number_field" style="order: ' . $number_flex_order . ';">';

                    if(isset($field['options']['number_field']['placeholder'])) {
                      $placeholder = $field['options']['number_field']['placeholder']; 
                    } else {
                      $placeholder = '';
                    }
                    $number_input = '<input type="number" min="' . $number_min . '" class="' . $field_class . '" name="'. $field['id'] . $field_id_array . '[number]" placeholder="' . $placeholder . '" value="'. $number_value . '" size="30" style="width:100%" />';

                    if(isset($field['options']['number_field']['label'])) {
                      echo '<label for="' . $field['id'] . $field_id_array . '[number]">' . $field['options']['number_field']['label'] . $number_input . '</label>';
                    } else {
                      echo $number_input;
                    }
                    echo '</div>';
                  }
                }
                if($is_repeater) {
                  echo '</div> <!-- .ds-wp-cpt-repeater-item -->';
                }
                $counter++;
              } // end foreach values
              if($is_repeater) {
                echo '<button class="ds-wp-cpt-repeater-add">Add Item</button></div> <!-- .ds-wp-cpt-repeater -->';
              }

          
            break;

          case 'image':
              echo $standardFieldLabel;
              echo CPTBuilder\CPT_InputFields::render_image_input($field['id'], $value_shown);
            break;
          case 'image_enhanced':

              global $post;

              // Check for repeater 
              $is_repeater = false;
              $field_id_array = '';
              if(isset($field['repeater']) ? $field['repeater'] : false) {
                $is_repeater = true;
              }

              // the repeater saved values are already an array so don't modify them
              if($is_repeater) {
                $values_array = $value_shown;
              } else {
                // add any existing values to an array
                $values_array = array($value_shown);
              }
              // if nothing was set we need to create the array so that there are no errors
              if(!(is_array($values_array))) {
                $values_array = array('');
              }
              echo $standardFieldLabel;

              // Check for repeater 
              $counter = '';

              if($is_repeater) {
                echo '<div class="ds-wp-cpt-repeater" data-id="' . $field['id'] . '">';
              }
              $counter = 0;
              foreach($values_array as $value_item) {

                if($is_repeater) {
                  $field_id_array = '[' . $counter . ']';
                } else {
                  $field_id_array = '';
                }
                // Get WordPress' media upload URL
                // $upload_link = esc_url( get_upload_iframe_src( 'image', $post->ID ) );

                if(!is_array($value_item)){
                  $image_id = $value_item;
                } else {
                  $image_id = isset($value_item['image_id']) ? $value_item['image_id']: '';
                }
                // Get the image src
                $ds_wp_cpt_attachment_img_src = wp_get_attachment_image_src( $image_id, 'full' );

                // For convenience, see if the array is valid
                $ds_wp_cpt_attachment_have_img = is_array( $ds_wp_cpt_attachment_img_src );

                // add a class to the removable div to show hover effects 
                $removeable_class = '';
                if( $ds_wp_cpt_attachment_have_img ) $removeable_class = 'has-image';

                // IMAGE PORTION
                $image_flex_order = isset($field['order']) ?
                $field['order'] : '1';
                if($is_repeater) {
                  echo '<div class="ds-wp-cpt-repeater-item"><button class="ds-wp-cpt-repeater-item-remove">Remove Item</button>';
                }
                echo '<div class="ds-wp-cpt-image-uploader ds-wp-cpt-uploader" style="order: ' .$image_flex_order . ';">';
                echo '<div class="ds-wp-cpt-image-uploader-removable ' . $removeable_class . '">';
                echo '<div class="ds-wp-cpt-remove"><span class="fa-stack"><i class="fa fa-circle fa-stack-1x icon-background"></i><i class="fa fa-times-circle fa-stack-1x"></i></span></div>';
                echo '<div class="ds-wp-cpt-image-uploader-image-container">';

                if ( $ds_wp_cpt_attachment_have_img ) {
                  echo '<img src="' . $ds_wp_cpt_attachment_img_src[0] . '" loading="lazy" alt="" style="max-width:100%;" />';
                } // end if ( $ds_wp_cpt_attachment_have_img ) 
                echo '</div>';

                echo '<!-- A hidden input to set and post the chosen image id -->
                      <input name="' . $field['id'] . $field_id_array . '[image_id]"  class="ds-wp-cpt-image-uploader-value" type="hidden"  value="'. $image_id . '"/>';
                echo '<button class="ds-wp-cpt-upload-button button" /><span class="no-image">Upload Image</span><span class="has-image">Change Image</span></button>';
                echo '</div>';
                echo '</div>';

                // TEXT FIELD PORTION
                if(isset($field['options'])) {

                  if(isset($field['options']['text_field'])) {
                    $text_value = isset($value_item['text']) ? $value_item['text'] : '';
                    $text_flex_order = isset($field['options']['text_field']['order']) ? $field['options']['text_field']['order'] : '1';

                    echo '<div class="ds-wp-cpt-image-options-text_field" style="order: ' .$text_flex_order . ';">';

                    if(isset($field['options']['text_field']['placeholder'])) {
                      $placeholder = $field['options']['text_field']['placeholder']; 
                    } else {
                      $placeholder = '';
                    }
                    $text_input = '<input type="text" class="' . $field_class . '" name="'. $field['id'] . $field_id_array . '[text]" placeholder="' . $placeholder . '" value="'. $text_value . '" size="30" style="width:100%" />';

                    if(isset($field['options']['text_field']['label'])) {
                      echo '<label for="' . $field['id'] . $field_id_array . '[text]">' . $field['options']['text_field']['label'] . $text_input . '</label>';
                    } else {
                      echo $text_input;
                    }
                    echo '</div>';
                  }
                  if(isset($field['options']['number_field'])) {
                    $number_value = isset($value_item['number']) ? $value_item['number'] : '';
                    $number_flex_order = isset($field['options']['number_field']['order']) ? $field['options']['number_field']['order'] : '2';
                    $number_min = isset($field['options']['number_field']['min']) ? $field['options']['number_field']['min'] : '1';

                    echo '<div class="ds-wp-cpt-image-options-number_field" style="order: ' . $number_flex_order . ';">';

                    if(isset($field['options']['number_field']['placeholder'])) {
                      $placeholder = $field['options']['number_field']['placeholder']; 
                    } else {
                      $placeholder = '';
                    }
                    $number_input = '<input type="number" min="' . $number_min . '" class="' . $field_class . '" name="'. $field['id'] . $field_id_array . '[number]" placeholder="' . $placeholder . '" value="'. $number_value . '" size="30" style="width:100%" />';

                    if(isset($field['options']['number_field']['label'])) {
                      echo '<label for="' . $field['id'] . $field_id_array . '[number]">' . $field['options']['number_field']['label'] . $number_input . '</label>';
                    } else {
                      echo $number_input;
                    }
                    echo '</div>';
                  }
                }
                // echo '</div>';
                if($is_repeater) {
                  echo '</div> <!-- .ds-wp-cpt-repeater-item -->';
                }
                $counter++;
              } // end foreach values
              if($is_repeater) {
                echo '<button class="ds-wp-cpt-repeater-add">Add Item</button></div> <!-- .ds-wp-cpt-repeater -->';
              }
            
            break;
          case 'gallery':

            global $post;

            // Get WordPress' media upload URL
            $upload_link = esc_url( get_upload_iframe_src( 'image', $post->ID ) );

            // See if there's a media id already saved as post meta
            $ds_wp_cpt_attachment_img_gallery_ids = get_post_meta( $post->ID, $field['id'], true );

            echo $standardFieldLabel;
            echo '<div class="ds-wp-cpt-image-gallery-uploader ds-wp-cpt-uploader">';

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
                    echo '<div class="ds-wp-cpt-remove"><span class="fa-stack"><i class="fa fa-circle fa-stack-1x icon-background"></i><i class="fa fa-times-circle fa-stack-1x"></i></span></div>';
                      echo '<img src="' . $ds_wp_cpt_attachment_img_gallery_src[0] . '" loading="lazy" alt="" />';
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
          case 'woocommerce_products':

            echo $standardFieldLabel; 
            if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {

              // check to make sure $value_shown is array. If not, make it an array
              if(!is_array($value_shown)){
                $value_shown = (array)$value_shown;
              }

              echo '<span class="ds-wp-cpt-metabox-wc-search-field"><select class="wc-product-search" multiple="multiple" style="width: 50%;" id="subscription_toggle_ids" name="' . $field['id']. '[]" data-placeholder="' . esc_attr( 'Search for a product&hellip;', 'woocommerce' ) . '" data-action="woocommerce_json_search_products_and_variations">';

                  foreach ( $value_shown as $product_id ) {
                    $product = wc_get_product( $product_id );
                    if ( is_object( $product ) ) {
                        echo '<option value="' . esc_attr( $product_id ) . '"' . selected( true, true, false ) . '>' . wp_kses_post( $product->get_formatted_name() ) . '</option>';
                    }
                }
                echo '</select></span>';
              } else {
                echo 'WooCommerce is not active.';
              }// end if is_plugin_active
              break;
            case 'woocommerce_categories':

                echo $standardFieldLabel; 
                if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {

                  // check to make sure $value_shown is array. If not, make it an array
                  if(!is_array($value_shown)){
                    $value_shown = (array)$value_shown;
                  }

                  $categories   = get_terms( 'product_cat', 'orderby=name&hide_empty=0' );

                  echo '<span class="ds-wp-cpt-metabox-wc-search-field"><select class="wc-enhanced-select" multiple="multiple" style="width: 50%;" id="subscription_toggle_ids" name="' . $field['id']. '[]" data-placeholder="' . esc_attr( 'Any Category', 'woocommerce' ) . '">';
                  if ( $categories ) {
                    foreach ( $categories as $cat ) {
                      echo '<option value="' . esc_attr( $cat->term_id ) . '"' . wc_selected( $cat->term_id, $value_shown ) . '>' . esc_html( $cat->name ) . '</option>';
                    }
                  }
                  echo '</select></span>';
              } else {
                echo 'WooCommerce is not active.';
              }// end if is_plugin_active

              break;
            // case 'repeater_text':
            //   echo $standardFieldLabel;
            //   echo '<div class="ds-cpt-repeater-array-container">';
            //     echo ' <input type="text" class="' . $field_class . '" name="'. $field['id']. '" id="'. $field['id'] .'" value="'. $value_shown . '" size="30" style="width:100%" ' . $readonly . '/>';


            //   echo '</div>';
            //   break;
            case 'multifield_repeater':
              echo $standardFieldLabel;
              $class = '';

              echo CPTBuilder\CPT_RepeaterFields::render_multifield_repeater($field['id'], $class, $value_shown, $field);
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
      //Check autosave and stop WP from clearing custom fields on autosave
      if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
          return $post_id;
      }

      if (!current_user_can('edit_page', $post_id) && !current_user_can('edit_post', $post_id)) {
          return $post_id;
      }

      foreach($meta_box_fields as $meta_box_values) {

        if(isset($meta_box_values['fields']) && is_array($meta_box_values['fields'])) {

          foreach($meta_box_values['fields'] as $field) {

            // check if a custom value is being used in which case we won't save
            if(isset($field['default']['custom_saved_value'])) {
              continue;
            }

            $existing_value = get_post_meta($post_id, $field['id'], true);

            // Remove any array [] symbols from our field ID name
            $field_id_cleaned = str_replace('[]', '', $field['id']);

            // check if the POST actually contains the field we're going to check against 
            $submitted_value = isset($_POST[$field_id_cleaned]) && !empty($_POST[$field_id_cleaned]) ? $_POST[$field_id_cleaned] : null;
            
            $sanitized_value = '';
            
            // check if the backup key was set
            $backups = isset($field['backups']) && $field['backups'] > 0 ? $field['backups'] : false;

            if(is_array($submitted_value)) {

              $sanitized_value = array();
              $sanitized_value = $this->sanitize_array($submitted_value, $field);
              $array_empty = false;
              $array_empty = $this->check_empty_array($sanitized_value);

              if($array_empty) {
                // remove the value since we don't want to store a blank array
                $sanitized_value = '';
              } 
        
            } else {
              $sanitized_value = $this->field_validation($submitted_value, $field);
            }

            $unique_key = isset($field['unique_key']) && $field['unique_key'] !== "" ? $field['unique_key'] : 'false';
            if($unique_key == 'true') {
              $sanitized_value = $this->validate_unique_key($sanitized_value,
              $field['id'], $field['label']);
            }
            //save the sanitized value or retain the existing one
            if (isset($sanitized_value) && $sanitized_value != $existing_value) {
              if($backups > 0) {
                $i = 1; 

                if($i == $backups) {
                  update_post_meta($post_id, $field['id'] . '_backup_1', $existing_value);
                } else {
                  while($i <= $backups) {
                    if($i == $backups) {
                      update_post_meta($post_id, $field['id'] . '_backup_1', $existing_value);
                    } else {
                      // move each backup up one
                      $backup_one_less = $backups - $i;
                      $backup_one_more = $backup_one_less + 1;
                      $existing_backup = get_post_meta($post_id, $field['id'] . '_backup_' . $backup_one_less, true);
                      update_post_meta($post_id, $field['id'] . '_backup_' . $backup_one_more, $existing_backup);
                      // global $wpdb;
                      // $wpdb->query("UPDATE `{$wpdb->base_prefix}postmeta` SET `meta_key` = '{$field['id']}_backup_{$h}' WHERE `meta_key` = '{$field['id']}_backup_{$i}'");
                    }
                    $i++;
                  }
                }
              } 
              // actually update into the right place
              update_post_meta($post_id, $field['id'], $sanitized_value);

            } elseif ('' == $sanitized_value && $existing_value) {
                delete_post_meta($post_id, $field['id'], $existing_value);
            } // end if ($new_value && $new_value != $existing_value)
          } //end foreach($meta_box_sections as $meta_box_values)
        } // end if(isset($meta_box_values['fields']) && is_array($meta_box_values['fields']))
      } // end foreach($meta_box_fields as $post => $meta_box_sections)
    } //end if (!isset($_POST['ds_wp_cpt_api_meta_box_nonce'])
  } // end function ds_wp_cpt_api_save_data

  /**
   * Sanitizes an array with the sanitize_text_feld option.
   * This will loop as deep as it needs through an array.
   */
  public function sanitize_array($array, $field) {

    $return_array = array();
    foreach ($array as $array_key => $array_item) {
      if(is_array($array_item)) {
        $return_array[$array_key] = $this->sanitize_array($array_item, $field);
      } else {
        $return_array[$array_key] = $this->field_validation($array_item, $field);
      }
    }

    return $return_array;
  }

  /**
   * Validates various fields
   */
  public function field_validation($submitted_value, $field) {
    // get the validation type. If not set, send the type of field
    $validation_type = isset($field['validation']) && !empty($field['validation']) ? $field['validation'] : $field['type'];
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
              $this->create_wp_error($field['id'], $submitted_value, $field['label'], 'There was an error uploading your file. The error is: ' . $upload['error']);
            } else {
              $sanitized_value = $upload;
            }
          } else {
            $this->create_wp_error($field['id'], $submitted_value, $field['label'], 'The file type that you\'ve uploaded is not a PDF.');
          } // end if(in_array($uploaded_type, $supported_types))
        } //end if(!$_FILES[$field['id']]['error'] == 4)
        break;
      // case 
      case 'number':
        $sanitized_value = $this->validate_number($_POST[$field['id']],
        $field['id'], $field['label']);
        break;
      case 'texteditor':
        $sanitized_value = $submitted_value;
        break;
      case 'textarea':
        if(isset($field['allow_html']) && $field['allow_html'] == true) {
          $allowed_html = $this->allowed_html();
          $sanitized_value = wp_kses($submitted_value, $allowed_html);
        } else {
          $sanitized_value = sanitize_text_field($submitted_value);
        }
      break;
      case 'api_key':
        $min = isset(	$field['validation_min'] ) ? $field['validation_min'] : 5;
        $max = isset(	$field['validation_max'] ) ? $field['validation_max'] : 20;
        $sanitized_value = $this->validate_api_key($_POST[$field['id']],
        $field['id'], $field['label'], $min, $max);
        break;
      case 'text_preserving_spaces':
        $sanitized_value = $this->validate_text_preserving_spaces($submitted_value);
        break;
      default:
        $sanitized_value = sanitize_text_field($submitted_value);
        break;

    } //end switch($validation_type)
    return $sanitized_value;
  }
  /** 
   * Check for empty arrays
   */
  public function check_empty_array($array) {
    foreach($array as $key => $value) {
      if(is_array($value)) {
        $empty_array_check = $this->check_empty_array($value);
        if(!$empty_array_check) {
          return false;
        }
      } else {
        if($value != '' && $value != null) {
          return false;
        }
      }
    }
    return true;
  }

  /** 
   * Allowed HTML fields
   */
  public function allowed_html() {

    $allowed_tags = array(
      'a' => array(
        'class' => array(),
        'href'  => array(),
        'rel'   => array(),
        'title' => array(),
      ),
      'abbr' => array(
        'title' => array(),
      ),
      'b' => array(),
      'blockquote' => array(
        'cite'  => array(),
      ),
      'cite' => array(
        'title' => array(),
      ),
      'code' => array(),
      'del' => array(
        'datetime' => array(),
        'title' => array(),
      ),
      'dd' => array(),
      'div' => array(
        'class' => array(),
        'title' => array(),
        'style' => array(),
      ),
      'dl' => array(),
      'dt' => array(),
      'em' => array(),
      'h1' => array(),
      'h2' => array(),
      'h3' => array(),
      'h4' => array(),
      'h5' => array(),
      'h6' => array(),
      'i' => array(),
      'iframe' => array(
        'src' => array(),
        'embed' => array(),
        'frameborder' => array(),
        'width' => array(),
        'height' => array(),
      ),
      'img' => array(
        'alt'    => array(),
        'class'  => array(),
        'height' => array(),
        'src'    => array(),
        'width'  => array(),
      ),
      'li' => array(
        'class' => array(),
      ),
      'ol' => array(
        'class' => array(),
      ),
      'p' => array(
        'class' => array(),
      ),
      'q' => array(
        'cite' => array(),
        'title' => array(),
      ),
      'span' => array(
        'class' => array(),
        'title' => array(),
        'style' => array(),
      ),
      'strike' => array(),
      'strong' => array(),
      'ul' => array(
        'class' => array(),
      ),
    );
    
    return $allowed_tags;
  }
  /**
   * validation for a unique key field
   */
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
    if((is_numeric($field_value)) || $field_value == ''){
      return sanitize_text_field($field_value);
    } else {
      $this->create_wp_error($field_id, $field_value, $label, 'make sure this field only contains a number');
    }
  } // end function validate_number

  /**
   * Allow spaces in a field
   */
  public function validate_text_preserving_spaces($field_value)  {
    $field_value = trim($field_value);
    $field_value = wp_check_invalid_utf8($field_value, true);
    return $field_value;
  }
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
  /**
     * @param string $filePath
     * @return string
     * Adapted from plugin-update-checker
     */
    public function get_file_url($filePath) {

      $absolutePath = realpath(dirname(__FILE__) . '/' . ltrim($filePath, '/'));
  
          //Where is the library located inside the WordPress directory structure?
          $absolutePath = wp_normalize_path($absolutePath);
          $pluginDir = wp_normalize_path(WP_PLUGIN_DIR);
          $muPluginDir = wp_normalize_path(WPMU_PLUGIN_DIR);
          $themeDir = wp_normalize_path(get_theme_root());
  
          if ( (strpos($absolutePath, $pluginDir) === 0) || (strpos($absolutePath, $muPluginDir) === 0) ) {
              //It's part of a plugin.
              return plugins_url(basename($absolutePath), $absolutePath);
          } else if ( strpos($absolutePath, $themeDir) === 0 ) {
              //It's part of a theme.
              $relativePath = substr($absolutePath, strlen($themeDir) + 1);
              $template = substr($relativePath, 0, strpos($relativePath, '/'));
              $baseUrl = get_theme_root_uri($template);
  
              if ( !empty($baseUrl) && $relativePath ) {
                  return $baseUrl . '/' . $relativePath;
              }
          } 
          return '';
      } // end function get_updater_url



}
} // END CLASS
