//v1.4.3

jQuery(function($) {

  $(document).ready(function() {
    /*
     * Scripts to show or hide sections of the CPT UI based upon
     * selected radio buttons or select menu options.
     * 
     * This version changes the behavior of prevous toggle classes
     * so they will need to be updated
     */

    function ShowHideFields() {

     var toggleTypeValue = $(this).val();
     var toggleClasses = $(this).attr('class');
     var toggleClassesArr = toggleClasses.split(' ');

     $(toggleClassesArr).each(function(index, current_class_value){

      if(current_class_value.startsWith('toggle_parent')){

        var child_class_value = current_class_value.replace('toggle_parent', 'toggle_child');

          //select all the types we want to show - they can be rows or entire
          //metabox sections
          var toggles = $('.ds-wp-cpt-metabox-settings-row.' + child_class_value + ', .postbox[id*="' + child_class_value + '"]');

          var togglesToShow = $('.ds-wp-cpt-metabox-settings-row.' + child_class_value + '.' + toggleTypeValue + ', .postbox[id*="' + child_class_value + '_' + toggleTypeValue + '"]');

          toggles.hide();
          togglesToShow.show();

          $(togglesToShow).each(function(){
            var selectors = $(this).find('input[class^="toggle_parent"][type="radio"]:checked,input[class*=" toggle_parent"][type="radio"]:checked,select[class^="toggle_parent"]:checked,select[class*=" toggle_parent"]:checked');
            $(selectors).each(function(){
              
              $(this).click();
            });
          })
       }
     });     
   }

    //show or hide the sections based on clicking the radio button or select
    $('input[class^="toggle_parent"][type="radio"],input[class*=" toggle_parent"][type="radio"],select[class^="toggle_parent"],select[class*=" toggle_parent"]').on('click', ShowHideFields );

    //show or hide the sections based on choosing a select option
    $('select[class^="toggle_parent"],select[class*=" toggle_parent"]').on('change', ShowHideFields );


    //Show or hide on page load
    $('input[class^="toggle_parent"][type="radio"]:checked,input[class*=" toggle_parent"][type="radio"]:checked,select[class^="toggle_parent"],select[class*=" toggle_parent"]').trigger( 'click' );

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
    $('.ds-wp-cpt-uploader-removable.has-media .ds-wp-cpt-remove').on('click', function() {
      $(this).parent().removeClass('has-media');
      console.log('cliiiddddd');
      $(this).parent().find($('.ds-wp-cpt-uploader-value')).val('');
      $(this).parent().find($('.ds-wp-cpt-file-name')).html('');
      $(this).parent().find($('.ds-wp-cpt-uploader-image-container')).html('');
    });
    $('.ds-wp-cpt-image-uploader-removable.has-image .ds-wp-cpt-remove').on('click', function() {
      $(this).parent().removeClass('has-image');
      $(this).parent().find($('.ds-wp-cpt-image-uploader-value')).val('');
      $(this).parent().find($('.ds-wp-cpt-image-uploader-image-container')).html('');
    });
    $('.ds-wp-cpt-image-gallery-uploader-removable.has-image .ds-wp-cpt-remove').on('click', function() {
      $(this).parent().remove();
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
