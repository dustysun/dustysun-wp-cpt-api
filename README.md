Dusty Sun WP CPT API
================

A class to make it easier to create custom post types in WordPress

THIS FILE NEEDS TO BE UPDATED WITH ALL OPTIONS. 

Features
--------
**Feature 1.**    

  Description

Getting Started
---------------

### Adding the class to your theme

Require the file:
```
require( dirname( __FILE__ ) . '/lib/ds_wp_cpt_api/ds_wp_cpt_api.php');
```

## Notes

Notes

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

### Toggling groups

If you add a class that starts with "toggle_" to a select or radio item, you can name other items with the same toggle class plus the name of the select or radio option. 

For example, if you had radio buttons with the options, apple, orange, and banana, along with the class "toggle_fruit_type" you could name additional input boxes with the classes "toggle_fruit_type apple", "toggle_fruit_type orange", and "toggle_fruit_type banana" to have only those elements shown when selecting one of the radio button or select options

## Changelog
#### 1.3.8 = 2019-02-22 
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