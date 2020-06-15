//v1.3.8

jQuery(function($) {

  $(document).ready(function() {
    /*
     * Scripts to show or hide sections of the CPT UI based upon
     * selected radio buttons or select menu options.
     */

    //function to show or hide or show input fields
    function ShowHideFields() {

     //get the value of the button clicked
     var toggleTypeValue = $(this).val();
     var toggleClasses = $(this).attr('class');

     // handle situations where there's more than one class
     toggleClasses = /(toggle_)\w+/.exec(toggleClasses)[0];

     var radioParent = $(this).parentsUntil('tr').parent();
     radioParent.addClass('show-option');

     //select all the types we want to show
     var toggles = $('.ds-wp-cpt-metabox-settings-row.' + toggleClasses);
     var togglesToShow = $('.ds-wp-cpt-metabox-settings-row.' + toggleClasses + '.' + toggleTypeValue);

     toggles.addClass('hide-option');
     togglesToShow.removeClass('hide-option');
   }

    //show or hide the sections based on clicking the radio button or choosing a select value
    $('input[class^="toggle_"][type="radio"],input[class^=" toggle_"][type="radio"]').on('click', ShowHideFields );
    $('select[class^="toggle_"]').on('change', ShowHideFields );

    //Show or hide on page load
    $('input[class^="toggle_"][type="radio"]:checked,input[class^=" toggle_"][type="radio"]:checked').trigger( 'click' );
    $('select[class^="toggle_"]').trigger('change');
    /*
     * Scripts to show the datepicker
     */
    //Load the datepicker on cpt date picker objects
    $('.ds-cpt-datepicker').datepicker();

    /*
     * Scripts to allow removal of items that were part of an array.
     *
     */
     $('.ds-cpt-removable-array-value').before().on('click', function() {
       $(this).remove();
     });

    /*
     * Scripts to allow removal of uploaded items
     *
     */
    $('.ds-wp-cpt-uploader-removable').before().on('click', function() {
      $(this).removeClass('has-media');
      $(this).find($('.ds-wp-cpt-uploader-value')).val('');
      $(this).find($('.ds-wp-cpt-file-name')).html('');
      $(this).find($('.ds-wp-cpt-uploader-image-container')).html('');
    });
    $('.ds-wp-cpt-image-uploader-removable').before().on('click', function() {
      $(this).removeClass('has-image');
      $(this).find($('.ds-wp-cpt-image-uploader-value')).val('');
      $(this).find($('.ds-wp-cpt-image-uploader-image-container')).html('');
    });
    $('.ds-wp-cpt-image-gallery-uploader-removable').before().on('click', function() {
      $(this).remove();
    });
    $('.ds-wp-cpt-image-uploader-image-gallery-container').sortable({tolerance:'pointer'});
    /*
     * Handle the link to the post in post title select
     */
     $('select.ds-wp-cpt-post-title-select').change(function(){
      link_name = $(this).attr('id') + '_link';
      $('#' + link_name).attr('href', '/wp-admin/post.php?post=' + $(this).val() + '&action=edit');
     });
  }); //end $(document).ready(funcion()


});
