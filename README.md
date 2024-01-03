Dusty Sun WP CPT API
================

A class to make it easier to create custom post types in WordPress


Features
--------

Easily create custom post types with standardized fields by using this class. 

Getting Started
---------------

### Adding the class to your theme

Require the file:
```
require( dirname( __FILE__ ) . '/lib/ds_wp_cpt_api/ds_wp_cpt_api.php');
```

### Instantiation

Example:
```
$my_api_settings = array(
  'json_file' => plugin_dir_path( __FILE__ ) . '/plugin-options.json',
  'register_settings' => true
);

$my_settings_page = new My_DustySun_WP_CPT_API($my_api_settings);
```

## Field Options

Readonly - set to true or false 

### Class

You can have groups toggled on and off. This version changes how this works, so please update any fields that simply have toggle_ and then their name.

Instead, in order to support multiple levels of hiding fields, please use toggle_parent_ and toggle_child_ in the names.

For example, with a radio button, add 'class' => 'toggle_parent_blankname' and have option values like 'option1' or 'option2'.

Next, with any of the possible field types you want toggle on and off (text, number, select, etc), simply add the class 'toggle_child_' that matches the parent name along with a class that matches the option value.

For example, you would give a text input that you want shown when option2 for the 'toggle_parent_blankname' the classes 'toggle_child_blankname option2'.

To hide an entire metabox section, you need to add everything to the end of the metabox ID. For example, for a metabox that you've named 'my_great_options' you would rename to 'my_great_options_toggle_child_blankname_option2'.

prepend_info = This option shows a line of info before settings fields. Accepts HTML.
info_blocks = This accepts an array to show info before settings fields. Accepts HTML.

### Field types

#### info
Displays some information that can't be changed.

#### text
Standard text input.

#### text_small 
A small-sized standard text input.

#### random_text 
Allows creating a randomized text field, good for passwords or keys.

#### number
A number input field.

#### textarea
Standard text area.

#### select
Standard select drop-down menu.
* options
** 'options' => array (
    'option_value' => 'option_label',
)
** 'multiple' - true or false. Sets select to allow multiple values.
** 'dynamic_options' - true or false. Used when values are filled dynamically by Select2, for instance. Assumes that the option value and label are the same.

#### radio
Standard radio buttons.

#### radio_on_off
Creates a toggle switch for an on/off, yes/no, etc. layout. Accepts only two options.

#### checkbox
Standard checkboxes.

#### texteditor
Adds the TinyMCE editor.

#### removable_display_array
Retrieves an array from the database and allows one or more items to be removed / x'ed out.

#### field_with_select
Adds a text input and a select menu; options are saved together.

#### post_title_select
Retrieves a listing of post titles and displays them in a select menu for the specified post type.

Options:
* post_type - list a string like 'page' or 'post'. Can also do multiples by listing an array like array('page', 'post')
* fields_shown - accepts an array. 
  ** 'title' - put a prefix if needed
  ** 'id' - lists post id - put a prefix if needed
  ** ''your_option_name' - if there's a post_meta item associated with it, you can list it here.
* orderby - list a field like 'title' or 'id' - defaults to 'title'
* order - list 'ASC' or 'DESC' - defaults to 'ASC'
  
#### pdfattachment
Allows uploading a PDF file.

#### media
Allows uploading any allowable media type.

#### media_enhanced
Same as the regular media type except you can specify an options key to add a text field or number field. This may expand to other types in the future.

If the key repeater set to true is added, allows adding the fields repeatedly.

Example: 
```
      array(
          'id' => 'tss_dslp_game_food_sound_01',
          'label' => 'Food Hive #1',
          'type' => 'media_enhanced',
          'class' => 'monster_food sound_file',
          'order' => 2,
          'repeater' => true,
          'options' => array(
              'text_field' => array(
                  'order' => 1,
                  'label' => 'Sound Label',
                  'placeholder' => 'Sound Placeholder' 
              ),
              'number_field' => array(
                  'order' => 2,
                  'label' => 'Sound Number',
                  'placeholder' => 0,
                  'min' => 1
              )
          )
      )
```

#### image
Allows uploading images.

#### image_enhanced
Same as the regular image type except you can specify an options key to add a text field or number field. This may expand to other types in the future.

If the key repeater set to true is added, allows adding the fields repeatedly.

Example: 
```
      array(
          'id' => 'tss_dslp_game_food_image_01',
          'label' => 'Food Hive #1',
          'type' => 'image_enhanced',
          'class' => 'monster_food food_image',
          'order' => 2,
          'repeater' => true,
          'options' => array(
              'text_field' => array(
                  'order' => 1,
                  'label' => 'Food Label',
                  'placeholder' => 'Food Placeholder' 
              ),
              'number_field' => array(
                  'order' => 2,
                  'label' => 'Food Number',
                  'placeholder' => 0,
                  'min' => 1
              )
          )
      )
```

#### multifield_repeater
This is in development as new options are added. Currently allows adding multiple types of inputs to a repeater object.

Example: 
```
array(
    'id' => '_tss_dslp_syllable_words',
    'label' => 'Syllable Words',
    'type' => 'multifield_repeater',
    'class' => 'syllable_words',
    'fields' => array(
        array(
            'id' => 'word',
            'label' => 'Word',
            'type' => 'text',
        ),
        array(
            'id' => 'syllables',
            'label' => 'Syllables',
            'help_text' => '(Example - ac_ti_vi_ty)',
            'type' => 'text',
        ),
        array(
            'id' => 'markers',
            'label' => 'Markers',
            'help_text' => '(Example - 100,150,200)',
            'type' => 'text',
        ),
        array(
            'id' => 'audio',
            'label' => 'Audio',
            'type' => 'media',
            'mime_type' => 'audio'
        ),
        array(
            'id' => 'image',
            'label' => 'Image',
            'type' => 'image',
        )

    ),
)
```

#### gallery
Allows creating a gallery of images; images can be removed individually or re-ordered.\


### Toggling groups

If you add a class that starts with "toggle_parent_" to a select or radio item, you can name other items with the same toggle class replacing parent with child, plus the name of the select or radio option. 

For example, if you had radio buttons with the options, apple, orange, and banana, along with the class "toggle_child_fruit_type" you could name additional input boxes with the classes "toggle_child_fruit_type apple", "toggle_child_fruit_type orange", and "toggle_child_fruit_type banana" to have only those elements shown when selecting one of the radio button or select options

## Changelog
#### 1.6.3 - 2024-01-02
* Added $text_domain field for use by CPTs.
* Fixed issue with function render_checkbox_input declaration under PHP 8.1.

#### 1.6.2 - 2023-08-24
* Fixed issues related to checkbox display and saving.
* Fixed issues with repeater JS.

#### 1.6.1 - 2023-08-05
* Fixed a few logic errors in settings.

#### 1.6.0 - 2023-04-25
* Bugfix related to namespaces.

#### 1.5.9 - 2023-02-02
* Fixed error message when there were no custom meta box fields set.

#### 1.5.8 - 2022-10-27
* Fix for htmlentities double encoding strings.

#### 1.5.7 - 2022-10-26
* Added lazy loading attribute for all images in the admin area.

#### 1.5.6 - 2022-08-25
* Added validation type 'text_preserving_spaces' to allow keeping spaces in a text field.
* Updated the validation to better handle arrays.
* Added a backups key to keep multiple backups of options in the postmeta table.

#### 1.5.5 - 2022-08-02
* Fixed issue with quotation marks in the value of a text field.

#### 1.5.4 - 2022-07-15
* Fixed issues with the ID names in the HTML for repeater items.
* Fixed issues with the repeater renumbering when the name contains dashes.

#### 1.5.3 - 2022-07-15
* Separated the radio box and checkbox types into the class-repeater_fields.php file.
* Added the radio box and checkbox type to the multifield repeater options.

#### 1.5.2 - 2022-05-24
* Fixed issues with the repeater renumbering items correctly.

#### 1.5.1 - 2021-11-29
* Changed the number validation to remove the requirement that a number be greater than 0 since negative values may sometimes be allowed

#### 1.5.0 - 2021-09-19
* Removed 97% width style for textareas.
* Added option for select items that have the multiple attribute.
* Added ability for select options to be filled dynamically which can happen if select2 is applied to a field.

#### 1.4.9 - 2021-06-03
* Added multiline_repeater type.
* Modified the file uploader JS to combine functions to make things easier to update. 
* Added option for mime_type for the media type so that video or audio or all media can be specified.

#### 1.4.8 - 2021-03-25
* Modified the "image_enhanced" type with a number field and a repeater option.
* Added a "media_enhanced" type with a repeater and text or number fields to match the "image_enhanced type. 

#### 1.4.7 - 2021-03-08
* Changed save function to also check for "edit_post" capability and not just "edit_page" while saving.

#### 1.4.6 - 2021-02-11
* Added options to add a text label field to the image type by specifying text_id and text_label.

#### 1.4.5 - 2021-01-16
* Fixed issue with the texteditor/wp_editor type.

#### 1.4.4 - 2020-12-03 
* Fixed the spacing of select drop downs.

#### 1.4.2 - 2020-06-13 
* Added ability to change the wp_editor options when using the "texteditor" type.

#### 1.4.1 - 2020-02-17
* Added ability to the 'post_type_select' field to sort by a specific field, either ascending or descending.
* Revised handling of the toggle_ class for inputs to allow multiple levels of groups to be shown or hidden.

#### 1.4 - 2019-05-15
* Added a woocommerce_products and woocommerce_categories type to show the WooCommerce product or category search.
* Updated the readme to better document the options available.

#### 1.3.9 - 2019-03-14
* Fixed issue with HTML not being properly rendered in a texteditor field

#### 1.3.8 - 2019-02-22 
* Added the ability to toggle fields with the select type
* Wrapped display values shown in esc_html

#### 1.3.7 - 2018-11-15 
* Fixed an issue with values not being saved in draft posts.

#### 1.3.6 - 2018-11-10 
* Fixed an error with saving number field with 0 as the value.
* Fixed an error with the hover effect on images.

#### 1.3.5 - 2018-10-11 
* Added gallery image type.
* Fixed errors in the radio and checkbox types.

#### 1.3.4 - 2018-08-30
* Updated the read only options for most fields.
