$(document).ready(function() {

    //prevent form submission by ENTER
    $(window).keydown(function(event) {
        if (event.keyCode === 13) {
            event.preventDefault();
            return false;
        }
    });

    //form elements
    $.fn.select2.defaults.set("theme", "bootstrap");
    $(".select2-input").select2();

    $('.customer').on("click", function() {
        var customer = $(this).attr('attr-customer');
        $.ajax({
            type: "POST",
            data: { 'customer': customer },
            beforeSend: function() {
                $("#spinnerModal").modal('show');
            },
            url: baseUrl + "email/ajax-dt-get-customers-expand-row",
            success: function(jObj) {
                $("#spinnerModal").modal("hide");
                if (jObj.successful) {
                    $("#customerRowModalContainer").html(jObj.html);
                    $("#customerRowModal").modal("show");
                }
            }
        });
    });


    $('.applicationReferrals').on("click", function() {
        $.ajax({
            type: "POST",
            data: { 'application': $(this).attr('attr-application') },
            beforeSend: function() {
                $("#spinnerModal").modal('show');
            },
            url: baseUrl + "referrals/ajax-load-application-summary-modal",
            success: function(jObj) {
                $("#spinnerModal").modal("hide");
                if (jObj.successful) {
                    $("#applicationSummaryModalContainer").html(jObj.html);
                    $("#applicationSummaryModal").modal("show");
                }
            }
        });
    });


    $('#showComposeEmailBtn').on("click", function() {
        if ($("#composeEmailDiv").length && $("#composeEmailDiv").css('display') == "none") {
            $('#composeEmailDiv').slideToggle();
        }

        scroll_to_element($("#composeEmailDiv"));
    });


    detect_cc_bcc_events();


    $('#itoolEmailTemplate').on('change', function() {
        // reload quick email content binding
        reload_quick_email_template_content();
    });


    $("#itoolEmailForm").on("click", "#iToolEmailSendBtn", function() {
        $(this).blur();

        var fromName = $('#itoolEmailFrom option:selected').attr('attr-from-name');
        $('#itoolEmailFromName').val(fromName);

        // required if saving from ajax
        tinyMCE.triggerSave(true, true);

        // Create a formdata object
        var dataset = new FormData(document.getElementById('itoolEmailForm'));

        //add the files if available
        if ($("#itoolEmailAttachment").val() !== "") {
            $.each($(':file'), function(i, file) {
                dataset.append('file-' + i, file);
            });
        }

        $.ajax({
            type: "POST",
            url: baseUrl + "email/ajax-submit-instant-tool-email",
            data: dataset,
            cache: false,
            processData: false, // Don't process the files
            contentType: false, // Set content type to false as jQuery will tell the server its a query string request
            beforeSend: function() {
                $("#spinnerModal").modal('show');
            },
            success: function(jObj) {
                $("#spinnerModal").modal('hide');
                if (parseInt(jObj.status) === 1) {
                    swal({ title: "", text: "Email Sent!", type: "success" }, function() {
                        location.reload(true);
                    });
                } else {
                    var err_msg = typeof jObj.error === 'undefined' || jObj.error === null ? 'Email Sending Failed!' : jObj.error;
                    swal('', err_msg, "error");
                }
            }
        });
    });

    $("#itoolEmailForm").on("click", "#itoolEmailFields", function() {
        if ($(this).html() == 'Show all fields') {
            $(this).html('Hide some fields');
        } else {
            $(this).html('Show all fields');
        }

        // hide some email fields
        $('#itoolEmailFromWrapper').slideToggle();
        $('#itoolEmailReplyToWrapper').slideToggle();
        $('#itoolEmailToWrapper').slideToggle();
        $('#itoolEmailAttachmentWrapper').slideToggle();
    });

    $('.btnAssignApplication').on("click", function() {
        $(this).blur();
        $.ajax({
            type: "POST",
            url: baseUrl + "email/ajax-load-assign-application",
            data: {
                'id': $(this).attr('attr-id'),
                'type': 'email'
            },
            success: function(jObj) {
                $("#instantToolsModalContainer").html(jObj.html);
                $("#instantToolsModalAssignApplication").modal("show");

                //form elements
                $.fn.select2.defaults.set("theme", "bootstrap");
                $(".select2-input").select2({
                    dropdownParent: $('#instantToolsModalAssignApplication')
                });

                showApplications(0);

                $("#itoolAssignApplicationForm").on("change", "#filterStatus", function() {
                    showApplications(0);
                });

                $("#itoolAssignApplicationForm").on("click", ".btnAssignPartner", function() {
                    $.ajax({
                        type: "POST",
                        data: {
                            'reference_code': $(this).attr('attr-reference-code'),
                            'log_id': $("#log_id").val(),
                            'type': 'email'
                        },
                        beforeSend: function() {
                            $("#spinnerModal").modal('show');
                        },
                        url: baseUrl + "email/ajax-assign-application",
                        success: function(jObj) {
                            $("#spinnerModal").modal("hide");
                            if (jObj.successful) {
                                swal({ title: "", text: "Email assigned to application!", type: "success" }, function() {
                                    location.reload(true);
                                });
                            } else {
                                var err_msg = typeof jObj.error === 'undefined' || jObj.error === null ? 'Assigning application Failed!' : jObj.error;
                                swal('', err_msg, "error");
                            }
                        }
                    });
                });
            }
        });
    });

    $('#itoolEmailMessage').tinymce({
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
                        text: 'Workspace Code',
                        onAction: function() {
                            editor.insertContent('[PARTNERCODE]');
                        }
                    }, {
                        type: 'menuitem',
                        text: 'Workspace Name',
                        onAction: function() {
                            editor.insertContent('[PARTNERNAME]');
                        }
                    }, {
                        type: 'menuitem',
                        text: 'Workspace URL',
                        onAction: function() {
                            editor.insertContent('[PARTNERURL]');
                        }
                    }, {
                        type: 'menuitem',
                        text: 'Workspace Hotline',
                        onAction: function() {
                            editor.insertContent('[PARTNERHOTLINE]');
                        }
                    }, {
                        type: 'menuitem',
                        text: 'Workspace Support',
                        onAction: function() {
                            editor.insertContent('[PARTNERSUPPORT]');
                        }
                    }];

                    callback(items);
                }
            });
        }
    });
});


function init() {}


function reload_quick_email_template_content() {
    $.ajax({
        type: "POST",
        url: baseUrl + "email/ajax-load-instant-tool-email-template",
        data: {
            'application': $('#itoolEmailApplication').val(),
            'partner': $('#itoolEmailPartner').val(),
            'user': $('#itoolEmailUser').val(),
            'from': $('#itoolEmailFrom').val(),
            'to': $('#itoolEmailTo').val(),
            'app_first_name': $('#itoolAppFirstName').val(),
            'app_partner_hotline': $('#itoolAppPartnerHotline').val(),
            'app_ref_code': $('#itoolAppRefCode').val(),
            'app_user_name': $('#itoolAppUserName').val(),
            'app_portal_name': $('#itoolAppPortalName').val(),
            'app_move_in_date': $('#itoolAppMoveInDate').val(),
            'app_email_template': $('#itoolEmailTemplate').val(),
            'app_full_name': $('#itoolAppFullName').val(),
            'app_new_address': $('#itoolAppNewAddress').val(),
            'app_partner_name': $('#itoolAppPartnerName').val()
        },
        beforeSend: function() {
            $("#spinnerModal").modal('show');
        },
        success: function(jObj) {
            $('#itoolEmailSubject').val(jObj.itoolEmailSubject);

            $('#itoolEmailMessage').val(jObj.itoolEmailBody);
            $("#spinnerModal").modal('hide');
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            $('#itoolEmailSubject').val("");
            $('#itoolEmailMessage').val('');
            $("#spinnerModal").modal('hide');
        }
    });
}

function showApplications(pageNo) {
    var filterStatus = $('#filterStatus').val();
    $.ajax({
        type: "GET",
        url: baseUrl + "email/ajax-reload-application/" + pageNo + "?filterStatus=" + filterStatus,
        success: function(jObj) {
            $('#appliction-wrapper').html(jObj.html);
            $('#pagination_application').html(jObj.pagination);

            $('#pagination_application').on('click', 'a', function(e) {
                e.preventDefault();
                var pageNo = $(this).attr('data-ci-pagination-page');
                showApplications(pageNo);
            });
        }
    });
}