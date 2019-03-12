// v1.3.5

jQuery(function($){
  //help from http://donnapeplinskie.com/blog/multiple-instances-wordpress-media-uploader/
  //https://codestag.com/how-to-use-wordpress-3-5-media-uploader-in-theme-options/
  //https://mikejolley.com/2012/12/21/using-the-new-wordpress-3-5-media-uploader-in-plugins/
  //https://codex.wordpress.org/Javascript_Reference/wp.media
  var imageUploadFrame = null;
  function showImageUploader() {
    //get our button object
    var self = this;
    //create the media frame if needed
    if(!imageUploadFrame) {
      // Create a new media frame
      imageUploadFrame = wp.media.frames.file_frame = wp.media({
        title: 'Select or Upload Media',
        button: {
          text: 'Use this media'
        },
        library: {
          type: 'image',
        },
        multiple: false  // Set to true to allow multiple files to be selected
      });
    } //end if

    //Remove any existing event handlers
    imageUploadFrame.off('select');
    //Now open the frame
    imageUploadFrame.on('select', function() {

      // Get media attachment details from the frame state
      var attachment = imageUploadFrame.state().get('selection').first().toJSON();

      var button = $(self);
      var fieldIdInput = '#' + button.attr('id').replace('_button', '');

      var imgContainer = ( fieldIdInput + '-img-container');

      // add a class to the removable div to show the hover effects 
      $(imgContainer).parent().addClass('has-image');

      // Send the attachment URL to our custom image input field.
      $(imgContainer).html( '<img src="'+attachment.url+'" alt="" style="max-width:100%;"/>' );

      // Send the attachment id to our hidden input
      $(fieldIdInput).val( attachment.id );
    });

    imageUploadFrame.open();

  } //end showImageUploader

  var imageGalleryUploadFrame = null;
  function showImageGalleryUploader() {
    //get our button object
    var self = this;
    //create the media frame if needed
    if(!imageGalleryUploadFrame) {
      // Create a new media frame
      imageGalleryUploadFrame = wp.media.frames.file_frame = wp.media({
        title: 'Select or Upload Media',
        button: {
          text: 'Use this media'
        },
        library: {
          type: 'image',
        },
        multiple: true  // Set to true to allow multiple files to be selected
      });
    } //end if

    //Remove any existing event handlers
    imageGalleryUploadFrame.off('select');
    //Now open the frame
    imageGalleryUploadFrame.on('select', function() {

      // Get media attachment details from the frame state
      var attachments = imageGalleryUploadFrame.state().get('selection').map(
        function(attachment) {
          attachment.toJSON();
          return attachment;
        }
      );
      var button = $(self);
      var fieldIdInput = button.attr('id').replace('_button', '');

      var imgGalleryContainer = ( '#' + fieldIdInput + '-img-gallery-container');

      // add a class to the removable div to show the hover effects 
      $(imgGalleryContainer).parent().addClass('has-image');
      var i = 0;
      var imageI = $( '#' + fieldIdInput + '-counter').val();

      for (i = 0; i < attachments.length; ++i) {

        var url_of_img = '';

        if('thumbnailsasdfasd' in attachments[i].attributes.sizes){
          url_of_img = attachments[i].attributes.sizes.thumbnail.url;
        } else if('full' in attachments[i].attributes.sizes){
          url_of_img = attachments[i].attributes.sizes.full.url;
        } 

        if(url_of_img != '') {
          $(imgGalleryContainer).append('<div id="' + fieldIdInput + imageI + '-container" class="ds-wp-cpt-image-gallery-uploader-removable has-image"></div>');

          var imgGalleryContainerRemoveable = $( '#' + fieldIdInput + imageI + '-container' );
          // Send the attachment URL to our custom image input field.
          $(imgGalleryContainerRemoveable).append( '<img src="'+ url_of_img +'" alt="" style="max-width:100%;"/>' );
          $(imgGalleryContainerRemoveable).append( '<input name="' + fieldIdInput + '[' + imageI + ']" id="' + fieldIdInput + imageI + '" class="ds-wp-cpt-image-gallery-uploader-value" type="hidden"  value="' + attachments[i].id + '"/>');
          ++imageI;
          $( '#' + fieldIdInput + '-counter').val(imageI);  
        }
      } // end for
 
    });

    imageGalleryUploadFrame.open();

  } //end showImageGalleryUploader

  var fileUploadFrame = null;
  function showMediaUploader() {
    //get our button object
    var self = this;
    //create the media frame if needed
    if(!fileUploadFrame) {
      // Create a new media frame
      fileUploadFrame = wp.media.frames.file_frame = wp.media({
        title: 'Select or Upload Media',
        button: {
          text: 'Use this media'
        },
        multiple: false  // Set to true to allow multiple files to be selected
      });
    } //end if

    //Remove any existing event handlers
    fileUploadFrame.off('select');
    //Now open the frame
    fileUploadFrame.on('select', function() {

      // Get media attachment details from the frame state
      var attachment = fileUploadFrame.state().get('selection').first().toJSON();

      var button = $(self);
      var fieldIdInput = '#' + button.attr('id').replace('_button', '');

      // see if it's an image or a media file 
      if(attachment.url.match(/\.(jpeg|jpg|gif|png)$/)) {
        var attachment_url = attachment.url;
      } else {
        var attachment_url = attachment.icon;
      }
      var mediaImgContainer = ( fieldIdInput + '-img-container');

      // add a class to the removable div to show the hover effects 
      $(mediaImgContainer).parent().addClass('has-media');
      
      // Send the attachment URL to our custom image input field.
      $(mediaImgContainer).html( '<img src="'+attachment_url+'" alt="" style="max-width:100%;"/>' );
      
      var linkFieldText = ( fieldIdInput + '-file-name');
      $(linkFieldText).html(attachment.url);

      // Send the attachment id to our hidden input
      $(fieldIdInput).val( attachment.id );
    });

    fileUploadFrame.open();

  } //end showMediaUploader


  $(function() { //Wait for the DOM
    $('.ds-wp-cpt-image-uploader .button').on( 'click', showImageUploader);
    $('.ds-wp-cpt-image-gallery-uploader .button').on( 'click', showImageGalleryUploader);
    $('.ds-wp-cpt-uploader .button').on('click', showMediaUploader);
  });

});
