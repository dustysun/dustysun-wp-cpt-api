// v1.5.4

jQuery(function($){
  //help from http://donnapeplinskie.com/blog/multiple-instances-wordpress-media-uploader/
  //https://codestag.com/how-to-use-wordpress-3-5-media-uploader-in-theme-options/
  //https://mikejolley.com/2012/12/21/using-the-new-wordpress-3-5-media-uploader-in-plugins/
  //https://codex.wordpress.org/Javascript_Reference/wp.media
  var imageUploadFrame = null;
  var fileUploadFrame = null;
  var imageGalleryUploadFrame = null;
  var audioUploadFrame = null;
  var videoUploadFrame = null;

  /**
   * Show an image gallery
   */
  function showImageGalleryUploader(button) {
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
      // var button = $(self);
      var fieldIdInput = button.attr('id').replace('_button', '');

      var imgGalleryContainer = ( '#' + fieldIdInput + '-img-gallery-container');

      // add a class to the removable div to show the hover effects 
      $(imgGalleryContainer).parent().addClass('has-image');
      var i = 0;
      var imageI = $( '#' + fieldIdInput + '-counter').val();

      for (i = 0; i < attachments.length; ++i) {

        var url_of_img = '';

        if('thumbnails' in attachments[i].attributes.sizes){
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

  /**
   * Set the parameters to show the image uploader
   */
  function showImageUploader(button) {
    var params = {
      title: 'Select or Upload Image',
      button: {
        text: 'Use this image'
      },
      library: {
        type: 'image',
      },
      multiple: false  // Set to true to allow multiple files to be selected
    };

    showUploader(button, params, 'image', 'imageUploadFrame');
  }

  /**
   * Set the parameters to show the media uploader
   */
  function showMediaUploader(button) {
    if($(button).data('mime_type') == 'all') {
      var params = {
        title: 'Select or Upload Media',
        button: {
          text: 'Use this media'
        },
        multiple: false,  // Set to true to allow multiple files to be selected
      };
  
      showUploader(button, params, 'media', 'fileUploadFrame');
    } else if($(button).data('mime_type') == 'audio') {
      var params = {
        title: 'Select or Upload Audio File',
        button: {
          text: 'Use this audio file'
        },
        multiple: false,  // Set to true to allow multiple files to be selected
        library: { type: 'audio'}
      };
  
      showUploader(button, params, 'media', 'audioUploadFrame');
    } else if($(button).data('mime_type') == 'video') {
      var params = {
        title: 'Select or Upload Video File',
        button: {
          text: 'Use this video file'
        },
        multiple: false,  // Set to true to allow multiple files to be selected
        library: { type: 'video'}
      };
  
      showUploader(button, params, 'media', 'videoUploadFrame');
    }
  }


  /**
   * Opens the actual media uploader
   * @param {*} params - the button that was clicked
   * 
   * @param {*} params - the wp.media array that defines what will be in the 
   * uploader itself
   * 
   * @param {*} type - image or media
   * 
   * @param {*} frameVariable - the name of the uploader frame variable
   */
  function showUploader(button, params, type = 'image', frameVariable = 'fileUploadFrame') {
      //create the media frame if needed
      if(!window[frameVariable]) {
        window[frameVariable] = wp.media.frames.file_frame = wp.media(params);
      } //end if
  
    
      //Remove any existing event handlers
      window[frameVariable].off('select');
      //Now open the frame
      window[frameVariable].on('select', function() {

      // Get media attachment details from the frame state
      var attachment = window[frameVariable].state().get('selection').first().toJSON();


      // see if it's an image or a media file 
      if(attachment.url.match(/\.(jpeg|jpg|gif|png)$/)) {
        var attachment_url = attachment.url;
      } else {
        var attachment_url = attachment.icon;
      }
      var mediaImgContainer = $(button).parent().find('.ds-wp-cpt-image-uploader-image-container');

      if(type == 'image') {
        var fieldIdInput = $(button).parent().find('.ds-wp-cpt-image-uploader-value'); 
        // add a class to the removable div to show the hover effects 
        $(mediaImgContainer).parent().addClass('has-image');
      
      } else {
        var fieldIdInput = $(button).parent().find('.ds-wp-cpt-uploader-value');
        // add a class to the removable div to show the hover effects 
        $(mediaImgContainer).parent().addClass('has-media');
      
      }

      // Send the attachment URL to our custom image input field.
      $(mediaImgContainer).html( '<img src="'+attachment_url+'" alt="" style="max-width:100%;"/>' );
      
      if(type == 'media') {
        var linkFieldText = $(button).parent().find('.ds-wp-cpt-file-name');
        $(linkFieldText).html(attachment.url);
      }

      // Send the attachment id to our hidden input
      $(fieldIdInput).val( attachment.id );
    });

    window[frameVariable].open();

  } //end showUploader


  $(function() { //Wait for the DOM
    $(document).on('click', '.ds-wp-cpt-image-uploader .button', function(e) {
      e.preventDefault();
      showImageUploader($(this))
    });

    $('.ds-wp-cpt-image-gallery-uploader .button').on( 'click', function(e) {
      e.preventDefault();
      showImageGalleryUploader($(this))
    });

    $(document).on('click', '.ds-wp-cpt-media-uploader .button', function(e) {
      e.preventDefault();
      showMediaUploader($(this))
    });
  });

});
