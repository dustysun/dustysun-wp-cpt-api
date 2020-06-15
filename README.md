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

You can have groups toggled on and off. This version changes how this works, so please update any fields that simply have toggle_ and then their name.

Instead, in order to support multiple levels of hiding fields, please use toggle_parent_ and toggle_child_ in the names.

For example, with a radio button, add 'class' => 'toggle_parent_blankname' and have option values like 'option1' or 'option2'.

Next, with any of the possible field types you want toggle on and off (text, number, select, etc), simply add the class 'toggle_child_' that matches the parent name along with a class that matches the option value.

For example, you would give a text input that you want shown when option2 for the 'toggle_parent_blankname' the classes 'toggle_child_blankname option2'.

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

#### image
Allows uploading images.

#### gallery
Allows creating a gallery of images; images can be removed individually or re-ordered.\


### Toggling groups

If you add a class that starts with "toggle_" to a select or radio item, you can name other items with the same toggle class plus the name of the select or radio option. 

For example, if you had radio buttons with the options, apple, orange, and banana, along with the class "toggle_fruit_type" you could name additional input boxes with the classes "toggle_fruit_type apple", "toggle_fruit_type orange", and "toggle_fruit_type banana" to have only those elements shown when selecting one of the radio button or select options

## Changelog

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
