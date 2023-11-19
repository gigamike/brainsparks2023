/*
 *  mhub-autocrop - v1.0.0
 *  jQuery Plugin for choosing, resizing, cropping and uploading photo
 * 
 *   Usage: 
 *
 * 	 $('#div-name').autocrop(options);
 * 
 *   
 */
;( function( $, window, document, undefined ) {

	"use strict";

		// defaults
		var pluginName = "autocrop",
			defaults = {
                elt_prefix: 'mhub-autocrop-',
                autocrop_modal_id: 'autocrop-modal-container',
                preview_image_id: 'autocrop-preview-image',
                cropBoxResizable: true,
                requiredWidth: 280,
                requiredHeight: 80,
				        onImageCropped: function(img){ 
                  console.log(img);
                },
                onError: function(data){ 
                  console.log(data);
                }    
          };

		// plugin constructor
		function Plugin ( element, options ) {
			
      this.element = element;
      this.settings = $.extend( {}, defaults, options );
			this._defaults = defaults;
			this._name = pluginName;
			this.internalState = {};
      this.cropper_instance = null;
      this.selected_files = null;
      this.current_file = null;

      this.uploaded_file_name = null;
      this.uploaded_file_size = null;

			this.init();
		}

		/* Avoid Plugin.prototype conflicts */
		$.extend( Plugin.prototype, {

			init: function() {
				
				$(this.element).on('change', this._onChange.bind(this));
                this._bindControls();
			
			}, /* init */ 

            _bindControls: function(){
                  $('#btnAutocropZoomIn').click(function(){
                    $('#'+ this.settings.preview_image_id).cropper("zoom", 0.1);
                  }.bind(this));
          
                  $('#btnAutocropZoomOut').click(function(){
                    $('#'+ this.settings.preview_image_id).cropper("zoom", -0.1);
                  }.bind(this));
          
                  $('#btnAutocropRotateLeft').click(function(){
                    $('#'+ this.settings.preview_image_id).cropper("rotate", -45);
                  }.bind(this));
          
                  $('#btnAutocropRotateRight').click(function(){
                    $('#'+ this.settings.preview_image_id).cropper("rotate", 45);
                  }.bind(this));

                  $('#btnAutocropUpload').click(this._onPhotoUpload.bind(this));
                  $('#btnAutocropUploadRaw').click(this._onPhotoUploadRaw.bind(this));
            },
            
            _onChange: function(e){

                // store the selected file
                this.selected_files = e.target.files;

                this.current_file = this.selected_files[0];

                // bind actions when the modal is displayed
                $('#'+ this.settings.autocrop_modal_id).on('show.bs.modal', this._onModalShown.bind(this));

                // show the modal
                $('#'+ this.settings.autocrop_modal_id).modal('show');
			},

            _onModalShown : function(){
                
                var reader;
                var file;

                if (this.selected_files && this.selected_files.length > 0) {
                  file = this.selected_files[0];

                  this.uploaded_file_name = file.name;
                  this.uploaded_file_size = file.size;

                  if (URL) {
                    this._onDoneReadingFile(URL.createObjectURL(file));
                  } else if (FileReader) {
                    reader = new FileReader();
                    reader.onload= function (e) {
                      this._onDoneReadingFile(reader.result);
                    }.bind(this);
                    reader.readAsDataURL(file);
                  }
                }

            }, // onModalShown

            _onDoneReadingFile: function (url) {
                var preview_image = document.getElementById(this.settings.preview_image_id);
                this.element.value = '';
                preview_image.src = url;
                this._configureCropper();
            }, 

            _configureCropper: function(){

                var preview_image = $('#'+ this.settings.preview_image_id);
                preview_image.cropper('destroy');
                var ratio = this._calculateRatio(
                                    this.settings.requiredWidth, 
                                    this.settings.requiredHeight, 
                                );

                preview_image.cropper({
                    aspectRatio: ratio,
                    minCropBoxWidth     : 100,
                    minCropBoxHeight    : 100,
                    minContainerHeight  : 400,
                    minContainerWidth   : 723,
                    minCanvasWidth      : 700,
                    minCanvasHeight     : 400,
                    cropBoxResizable    : this.settings.cropBoxResizable
                });
            
                // Get the Cropper.js instance after initialized
                this.cropper_instance = preview_image.data('cropper');
            },


            _calculateRatio: function(num_1, num_2){

                for(var num=num_2; num>1; num--) {
            
                    if((num_1 % num) == 0 && (num_2 % num) == 0) {
                        num_1=num_1/num;
                        num_2=num_2/num;
                    }
            
                }
                var ratio = num_1/num_2;
                return ratio;
            },

            _onPhotoUpload: function(){

              var canvas;
              var uploaded_filename = this.uploaded_file_name; 
  
              if (this.cropper_instance) {
                canvas = this.cropper_instance.getCroppedCanvas({
                  width: 500,
                  height: 500,
                });
      
                if(canvas == null)
                {
                  console.log('canvas is null!');
                  return;
                }
                canvas.toBlob(function (blob) {
      
                  if(blob.size > 2000000){
                    swal('', 'Please select smaller area. We have limit of 2MB for image size', "error");
                    return;
                  }
      
                  var formData = new FormData();
      
                  formData.append('input_photo', blob, uploaded_filename);
                  $.ajax( baseUrl + 'photo/ajax_save', {
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
      
                    xhr: function () {
                      var xhr = new XMLHttpRequest();
      
                      xhr.upload.onprogress = function (e) {
                        var percent = '0';
                        var percentage = '0%';
      
                        if (e.lengthComputable) {
                          percent = Math.round((e.loaded / e.total) * 100);
                          percentage = percent + '%';
                        }
                      };
      
                      return xhr;
                    },
      
                    success: this._onPhotoUploadComplete.bind(this),
                    error: this._onPhotoUploadError.bind(this)
                  });
                }.bind(this));
              }
            }, // photo Upload

            _onPhotoUploadComplete: function(data){
              if(data.successful != undefined && data.successful == true)
                  this.settings.onImageCropped(data.photo);
              else
                 this.settings.onError(data);
            },

            _onPhotoUploadError: function(data){
                this.settings.onError(data);
            },
            _onPhotoUploadRaw: function(){

              if(this.current_file == null)
                return;

              var formData = new FormData();
      
              formData.append('input_photo', this.current_file);
              $.ajax( baseUrl + 'photo/ajax_save', {
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
  
                xhr: function () {
                  var xhr = new XMLHttpRequest();
  
                  xhr.upload.onprogress = function (e) {
                    var percent = '0';
                    var percentage = '0%';
  
                    if (e.lengthComputable) {
                      percent = Math.round((e.loaded / e.total) * 100);
                      percentage = percent + '%';
                    }
                  };
  
                  return xhr;
                },
  
                success: this._onPhotoUploadComplete.bind(this),
                error: this._onPhotoUploadError.bind(this)
              });
            }, // photo Upload
            
		
		} ); // extend


		$.fn[ pluginName ] = function( options, args ) {
			return this.each( function() {
				var $plugin = $.data( this, "plugin_" + pluginName );
				if (!$plugin) {
					var pluginOptions = (typeof options === 'object') ? options : {};
					$plugin = $.data( this, "plugin_" + pluginName, new Plugin( this, pluginOptions ) );
				}
				
				if (typeof options === 'string') {
					if (typeof $plugin[options] === 'function') {
						if (typeof args !== 'object') args = [args];
						$plugin[options].apply($plugin, args);
					}
				}
			} );
		};

} )( jQuery, window, document );