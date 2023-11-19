var formValidator = null;

$(document).ready(function() {

    //prevent form submission by ENTER
    $(window).keydown(function(event) {
        if (event.keyCode === 13) {
            event.preventDefault();
            return false;
        }
    });

    $.fn.select2.defaults.set("theme", "bootstrap");
    $(".select2-input").select2();

    var rules_options = {
        name: { required: true },
        subject: { required: true }
    };

    formValidator = $("#emailTemplateForm").validate({
        rules: rules_options,
        ignore: [":disabled", ":hidden"],
        highlight: function(element) {
            $(element).closest('.form-group').addClass('has-error');
        },
        unhighlight: function(element) {
            $(element).closest('.form-group').removeClass('has-error');
        },
        errorClass: 'help-block',
        errorPlacement: function(error, element) {
            if (element.parent('.input-group').length) {
                error.insertAfter(element.parent());
            } else {
                error.insertAfter(element);
            }
        }

    });

    $('#resetBtn').click(function() {
        $(this).blur();

        $("#emailTemplateForm")[0].reset();
    });

    $('#saveBtn').click(function() {
        $(this).blur();

        //validate form
        if (!$("#emailTemplateForm").valid()) {
            return;
        }

        //save
        $.ajax({
            type: 'POST',
            url: baseUrl + 'workflow-builder/ajax-email-template-save',
            data: $("#emailTemplateForm").serialize(),
            beforeSend: function() {
                $("#spinnerModal").modal('show');
            },
            success: function(jObj) {
                $("#spinnerModal").modal("hide");
                if (jObj.successful) {
                    swal({
                        title: "",
                        text: "Email template saved!",
                        type: "success",
                        confirmButtonColor: "#2C83FF",
                        confirmButtonText: "Close"
                    }, function() {
                        window.location.href = baseUrl + "workflow-builder/email-template";
                    });
                } else {
                    swal('', jObj.error, "error");
                }
            }
        });
    });

    $('#token').change(function() {
        var token = $(this).val();
        $('#subject').val($('#subject').val() + ' ' + token);

        $(this).val('');
    });
});




function init_load() {
    getTinymce();
}

function init_load_add() {
    init_load();
}

function init_load_update() {
    init_load();
}

function getTinymce() {
    tinymce.remove('#html_template');
    $('#html_template').tinymce({
        contextmenu: false,
        browser_spellcheck: true,
        height: 500,
        menubar: 'edit view insert format table tools',
        plugins: [
            'advlist autolink lists link image charmap print preview anchor',
            'searchreplace visualblocks code fullscreen',
            'insertdatetime media table paste code help wordcount',
            'autoresize codesample directionality emoticons fullpage hr legacyoutput nonbreaking pagebreak tabfocus textpattern visualchars imagetools'
        ],
        toolbar: [
            'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent',
            'removeformat | image | code | inserttoken | help'
        ],
        // relative_urls: false, // do not use this, when special tag i.e. [URL] it adds domain i.e. https://local-utilihub.io/[URL]
        // remove_script_host: true,
        // document_base_url: baseUrl,
        urlconverter_callback: function(url, node, on_save, name) {
            return url;
        },
        automatic_uploads: true,
        file_picker_types: 'image',
        //images_upload_url: baseUrl + 'common/ajax-tinymce-file-save',
        images_upload_handler: function(blobInfo, success, failure) {
            var xhr, formData;

            xhr = new XMLHttpRequest();
            xhr.withCredentials = false;
            xhr.open('POST', baseUrl + 'common/ajax-tinymce-file-save');

            xhr.onload = function() {
                var json;

                if (xhr.status != 200) {
                    failure('HTTP Error: ' + xhr.status);
                    return;
                }

                json = JSON.parse(xhr.responseText);

                if (!json || typeof json.location != 'string') {
                    failure('Invalid JSON: ' + xhr.responseText);
                    return;
                }

                success(json.location);
            };

            formData = new FormData();
            formData.append('file', blobInfo.blob(), blobInfo.filename());
            // append CSRF token in the form data
            formData.append('csrfmhub', $('#csrfheaderid').val());

            xhr.send(formData);
        },
        convert_urls: true,

        // https://www.tiny.cloud/docs/demo/custom-toolbar-menu-button/
        setup: function(editor) {
            editor.ui.registry.addMenuButton('inserttoken', {
                text: 'Insert Token',
                fetch: function(callback) {
                    var items = [{
                        type: 'menuitem',
                        text: 'Customer Fullname',
                        onAction: function() {
                            editor.insertContent('[FULLNAME]');
                        }
                    }, {
                        type: 'menuitem',
                        text: 'Customer Firstname',
                        onAction: function() {
                            editor.insertContent('[FIRSTNAME]');
                        }
                    }, {
                        type: 'menuitem',
                        text: 'Customer Lastname',
                        onAction: function() {
                            editor.insertContent('[LASTNAME]');
                        }
                    }, {
                        type: 'menuitem',
                        text: 'Customer Email',
                        onAction: function() {
                            editor.insertContent('[EMAIL]');
                        }
                    }, {
                        type: 'menuitem',
                        text: 'Customer Mobile Phone',
                        onAction: function() {
                            editor.insertContent('[MOBILEPHONE]');
                        }
                    }];

                    callback(items);
                }
            });
        }
    });
}