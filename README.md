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

Field Options

Readonly - set to true or false 

Class

You can have groups toggled on and off. 

For the radio button, add 'class' => 'toggle_blankname'.

For the inputs, add 'class' => 'specificradiovalue toggle_blankname'. If the radio button value is selected that matches the one shown for the input, it will be shown.

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

#### pdfattachment
Allows uploading a PDF file.

#### media
Allows uploading any allowable media type.

#### image
Allows uploading images.

#### gallery
Allows creating a gallery of images; images can be removed individually or re-ordered.\


### Toggling groups

If you add a class that starts with "toggle_" to a select or radio item, you can name other items with the same toggle class plus the name of the select or radio option. 

For example, if you had radio buttons with the options, apple, orange, and banana, along with the class "toggle_fruit_type" you could name additional input boxes with the classes "toggle_fruit_type apple", "toggle_fruit_type orange", and "toggle_fruit_type banana" to have only those elements shown when selecting one of the radio button or select options

## Changelog
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