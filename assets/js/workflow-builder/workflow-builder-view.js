var selectedFields = [];

$(document).ready(function() {
    //prevent form submission by ENTER
    $(window).keydown(function(event) {
        if (event.keyCode === 13) {
            event.preventDefault();
            return false;
        }
    });

    $("#workflowBuilderForm").css('visibility', 'visible');

    $("#workflowBuilderForm").steps({
        bodyTag: "fieldset",
        enableFinishButton: false,
        onStepChanging: function(event, currentIndex, newIndex) {
            // Always allow going backward even if the current step contains invalid fields!
            if (currentIndex > newIndex) {
                return true;
            }

            var form = $(this);

            // Clean up if user went backward before
            if (currentIndex < newIndex) {
                // To remove error styles
                $(".body:eq(" + newIndex + ") label.error", form).remove();
                $(".body:eq(" + newIndex + ") .error", form).removeClass("error");
            }

            // Disable validation on fields that are disabled or hidden.
            form.validate().settings.ignore = ":disabled,:hidden";

            // 1. Details currentIndex == 0

            // 2. Conditions
            if (currentIndex == 1) {
                if (form.valid()) {
                    var isMoveNext = true;
                    $.ajax({
                        type: 'POST',
                        async: false,
                        url: baseUrl + 'workflow-builder/ajax-workflow-builder-validate-fields',
                        data: $('#workflowBuilderForm').serialize(),
                        success: function(jObj) {
                            $('#dtApplicationSummaryTbl').DataTable().clear();
                            $('#dtApplicationSummaryTbl').DataTable().destroy();

                            if (jObj.successful == true) {
                                // 3. Preview
                                dtApplicationObj = $('#dtApplicationSummaryTbl').dataTable({
                                    // dom: '<<t><"row m-t-sm"<"col-sm-6"i><"col-sm-6"p>>>',
                                    dom: '<<t><"row m-t-sm"<"col-md-4 desktop-only"l><"col-md-4 text-center"i><"col-md-4"p>>>',
                                    processing: true,
                                    serverSide: true,
                                    responsive: false,
                                    autoWidth: false,
                                    scrollX: true,
                                    //scrollY: true,
                                    pageLength: 100,
                                    ajax: {
                                        type: "POST",
                                        "url": baseUrl + "workflow-builder/ajax-workflow-builder-dt-post-customers",
                                        "data": function(d) {
                                            d.filter = $('#workflowBuilderForm').serialize();
                                            d.csrfmhub = $('#csrfheaderid').val();
                                        }
                                    },
                                    columns: [
                                        { "data": "reference_code" },
                                        { "data": "full_name" },
                                        { "data": "email" },
                                        { "data": "date_of_birth" },
                                        { "data": "age" },
                                        { "data": "application_type", "searchable": false },
                                        { "data": "offer_type", "searchable": false },
                                        { "data": "application_status", "searchable": false },
                                        { "data": "status_tag", "searchable": false },
                                        { "data": "move_in_date", "searchable": false },
                                        { "data": "date_added", "searchable": false },
                                        { "data": "date_modified", "searchable": false }
                                    ],
                                    order: [
                                        [0, 'asc']
                                    ],
                                    language: {
                                        "search": "_INPUT_", //search
                                        "searchPlaceholder": "Search Records",
                                        "lengthMenu": "Show _MENU_", //label
                                        "emptyTable": "None Found.",
                                        "info": "Showing _START_ - _END_ of _TOTAL_", //label
                                        "paginate": { "next": "<i class=\"fa fa-angle-right\"></i>", "previous": "<i class=\"fa fa-angle-left\"></i>" } //pagination
                                    }
                                });

                                $(window).resize(function() {
                                    dtApplicationObj.fnAdjustColumnSizing();
                                });

                                $(".navbar-minimalize").click(function() {
                                    // add delay since inspinia.js adds delay in SmoothlyMenu()
                                    setTimeout(function() {
                                        dtApplicationObj.fnAdjustColumnSizing();
                                    }, 310);
                                });
                            } else {
                                isMoveNext = false;
                                swal('', jObj.error, "error");
                            }
                        }
                    });

                    if (!isMoveNext) {
                        return false;
                    }
                }
            }

            return true;
        },
        onStepChanged: function(event, currentIndex, priorIndex) {},
        onFinishing: function(event, currentIndex) {
            var form = $(this);

            // Disable validation on fields that are disabled.
            // At this point it's recommended to do an overall check (mean ignoring only disabled fields)
            form.validate().settings.ignore = ":disabled";

            return true;
        },
        onFinished: function(event, currentIndex) {}
    }).validate({

    });

    $('.actions ul > li:nth-child(1) a').addClass('btn btn-md btn-default  btn-w-sm m-t-xs m-l-xs');
    $('.actions ul > li:nth-child(2) a').addClass('btn btn-md btn-primary  btn-w-sm m-t-xs m-l-xs');
    $('.actions ul > li:last-child a').addClass('btn btn-md btn-primary  btn-w-sm m-t-xs m-l-xs');

    $.fn.select2.defaults.set("theme", "bootstrap");
    $(".select2-input").select2();

    //icheck
    $('.i-checks').iCheck({
        checkboxClass: 'icheckbox_square-blue',
        radioClass: 'iradio_square-blue'
    });

    //switchery
    if ($("#action_email_with_system_template").length) {
        switchery_active = document.querySelector('#action_email_with_system_template');
        new Switchery(switchery_active, { color: '#2C83FF' });
    }

    $(".textValue").on('change', function() {
        var field = $(this).closest('.conditionWrapper').find('.selectField').val();
        if (field == 'application_status') {
            $('.selectField').each(function(key, value) {
                if ($(this).val() == 'application_status_tag') {
                    $(this).closest('.conditionWrapper').remove();

                    selectedFields.remove('application_status_tag');
                }
            });
        }
    });

    displaySMSMessageCounter();

    /*
     $('.input-group.date.single').datepicker({
     format: baseDateFormat,
     autoclose: true,
     keyboardNavigation: false
     });

     $('.input-group.date.date-from').datepicker({
     format: baseDateFormat,
     autoclose: true,
     keyboardNavigation: false
     }).on('changeDate', function(e) {
     var container = $(this).closest('.conditionWrapper').find('.conditionValueWrapper');

     if (container.find('.date-from-field').val() == "") {
     return;
     }

     var from_date = new Date(e.date);
     var dateParts = container.find('.date-to-field').val().split("/");
     var to_date = new Date();

     // CODE BRANCHING HERE - DATE FORMAT
     if (baseDateFormat === "mm/dd/yyyy") {
     to_date.setFullYear(dateParts[2], parseInt(dateParts[0]) - 1, dateParts[1]);
     } else {
     //default format is dd/mm/yyyy
     to_date.setFullYear(dateParts[2], parseInt(dateParts[1]) - 1, dateParts[0]);
     }

     if (from_date > to_date) {
     $(this).datepicker('update', to_date);
     }
     });

     $('.input-group.date.date-to').datepicker({
     format: baseDateFormat,
     autoclose: true,
     keyboardNavigation: false
     }).on('changeDate', function(e) {
     var container = $(this).closest('.conditionWrapper').find('.conditionValueWrapper');

     if (container.find('.date-to-field').val() == "") {
     return;
     }

     var to_date = new Date(e.date);
     var dateParts = container.find('.date-from-field').val().split("/");
     var from_date = new Date();

     // CODE BRANCHING HERE - DATE FORMAT
     if (baseDateFormat === "mm/dd/yyyy") {
     from_date.setFullYear(dateParts[2], parseInt(dateParts[0]) - 1, dateParts[1]);
     } else {
     //default format is dd/mm/yyyy
     from_date.setFullYear(dateParts[2], parseInt(dateParts[1]) - 1, dateParts[0]);
     }

     if (to_date < from_date) {
     $(this).datepicker('update', from_date);
     }

     });

     $(".date-filter-operator").on('change', function() {
     var container = $(this).closest('.conditionWrapper').find('.conditionValueWrapper');

     //QUERY_FILTER_IS_BETWEEN = 13
     if (parseInt($(this).val()) === 13) {
     container.find(".date-filter-single-input-div").css('display', 'none');
     container.find(".date-filter-multi-input-div").css('display', 'block');

     container.find(".input-group.date > :input").val("");

     container.find('.input-group.date.date-single').datepicker('update', new Date()).datepicker('update', '');
     container.find('.input-group.date.date-from').datepicker('update', new Date()).datepicker('update', '');
     container.find('.input-group.date.date-to').datepicker('update', new Date()).datepicker('update', '');
     } else if (container.find(".date-filter-single-input-div").css('display') == "none") {
     container.find(".date-filter-single-input-div").css('display', 'block');
     container.find(".date-filter-multi-input-div").css('display', 'none');

     container.find(".input-group.date > :input").val("");
     container.find('.input-group.date.date-single').datepicker('update', new Date()).datepicker('update', '');
     container.find('.input-group.date.date-from').datepicker('update', new Date()).datepicker('update', '');
     container.find('.input-group.date.date-to').datepicker('update', new Date()).datepicker('update', '');
     }
     });
     */

    getTinymce();
});

function init() {
    action();
    fieldCleanup();
    execution();
}

function action() {
    var action_email_send = $('#action_email_send').iCheck('update')[0].checked;
    var action_sms_send = $('#action_sms_send').iCheck('update')[0].checked;
    var action_set_application = $('#action_set_application').iCheck('update')[0].checked;

    if (action_email_send) {
        $(".actionSendEmailWrapper").show();
    } else {
        $(".actionSendEmailWrapper").hide();
    }

    if (action_sms_send) {
        $(".actionSendSMSWrapper").show();
    } else {
        $(".actionSendSMSWrapper").hide();
    }

    if (action_set_application) {
        $(".actionSetApplicationStatusWrapper").show();
    } else {
        $(".actionSetApplicationStatusWrapper").hide();
    }
}

function execution() {
    switch ($('#execute').val()) {
        case 'every_day':
            $("#executeDayWrapper").hide();
            $("#executeMonthWrapper").hide();
            $("#executeAtWrapper").show();
            break;
        case 'every_week':
            $("#executeDayWrapper").show();
            $("#executeMonthWrapper").hide();
            $("#executeAtWrapper").show();
            break;
        case 'every_month':
            $("#executeDayWrapper").hide();
            $("#executeMonthWrapper").show();
            $("#executeAtWrapper").show();
            break;
        case 'every_5_minutes':
            $("#executeDayWrapper").hide();
            $("#executeMonthWrapper").hide();
            $("#executeAtWrapper").hide();
            break;
        case 'every_10_minutes':
            $("#executeDayWrapper").hide();
            $("#executeMonthWrapper").hide();
            $("#executeAtWrapper").hide();
            break;
        case 'every_15_minutes':
            $("#executeDayWrapper").hide();
            $("#executeMonthWrapper").hide();
            $("#executeAtWrapper").hide();
            break;
        case 'every_20_minutes':
            $("#executeDayWrapper").hide();
            $("#executeMonthWrapper").hide();
            $("#executeAtWrapper").hide();
            break;
        case 'every_25_minutes':
            $("#executeDayWrapper").hide();
            $("#executeMonthWrapper").hide();
            $("#executeAtWrapper").hide();
            break;
        case 'every_30_minutes':
            $("#executeDayWrapper").hide();
            $("#executeMonthWrapper").hide();
            $("#executeAtWrapper").hide();
            break;
        case 'every_1_hour':
            $("#executeDayWrapper").hide();
            $("#executeMonthWrapper").hide();
            $("#executeAtWrapper").hide();
            break;
        case 'every_2_hours':
            $("#executeDayWrapper").hide();
            $("#executeMonthWrapper").hide();
            $("#executeAtWrapper").hide();
            break;
        case 'every_3_hours':
            $("#executeDayWrapper").hide();
            $("#executeMonthWrapper").hide();
            $("#executeAtWrapper").hide();
            break;
        case 'every_4_hours':
            $("#executeDayWrapper").hide();
            $("#executeMonthWrapper").hide();
            $("#executeAtWrapper").hide();
            break;
        case 'every_5_hours':
            $("#executeDayWrapper").hide();
            $("#executeMonthWrapper").hide();
            $("#executeAtWrapper").hide();
            break;
        case 'every_6_hours':
            $("#executeDayWrapper").hide();
            $("#executeMonthWrapper").hide();
            $("#executeAtWrapper").hide();
            break;
        case 'every_7_hours':
            $("#executeDayWrapper").hide();
            $("#executeMonthWrapper").hide();
            $("#executeAtWrapper").hide();
            break;
        case 'every_8_hours':
            $("#executeDayWrapper").hide();
            $("#executeMonthWrapper").hide();
            $("#executeAtWrapper").hide();
            break;
        case 'every_9_hours':
            $("#executeDayWrapper").hide();
            $("#executeMonthWrapper").hide();
            $("#executeAtWrapper").hide();
            break;
        case 'every_10_hours':
            $("#executeDayWrapper").hide();
            $("#executeMonthWrapper").hide();
            $("#executeAtWrapper").hide();
            break;
        case 'every_11_hours':
            $("#executeDayWrapper").hide();
            $("#executeMonthWrapper").hide();
            $("#executeAtWrapper").hide();
            break;
        case 'every_12_hours':
            $("#executeDayWrapper").hide();
            $("#executeMonthWrapper").hide();
            $("#executeAtWrapper").hide();
            break;
        default:
            $("#executeDayWrapper").hide();
            $("#executeMonthWrapper").hide();
            $("#executeAtWrapper").hide();
    }
}

function displaySMSMessageCounter() {
    var text_length = $('#action_sms_message').val().length;
    var text_str = parseInt(text_length) > 0 ? text_length + ' characters' : '';
    $('#SMSMessageCounter').html(text_str);
}

Array.prototype.remove = function() {
    var what, a = arguments,
        L = a.length,
        ax;
    while (L && this.length) {
        what = a[--L];
        while ((ax = this.indexOf(what)) !== -1) {
            this.splice(ax, 1);
        }
    }
    return this;
};

/*
 * Remove Application Status Tag if Application Status is removed
 */
function fieldCleanup() {
    var isFieldApplicationStatusFound = false;
    $('.selectField').each(function(key, value) {
        if ($(this).val() == 'application_status') {
            isFieldApplicationStatusFound = true;
        }
    });

    if (!isFieldApplicationStatusFound) {
        $('.selectField').each(function(key, value) {
            if ($(this).val() == 'application_status_tag') {
                $(this).closest('.conditionWrapper').remove();

                selectedFields.remove('application_status_tag');
            }
        });
    }

    selectedFields = [];
    $('.selectField').each(function(key, value) {
        selectedFields.push($(this).val());
    });
}

function getTinymce() {
    tinymce.remove('#action_email_html_template');
    $('#action_email_html_template').tinymce({
        readonly: 1,
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
                        text: 'Customer Age',
                        onAction: function() {
                            editor.insertContent('[AGE]');
                        }
                    }, {
                        type: 'menuitem',
                        text: 'Customer Mobile Phone',
                        onAction: function() {
                            editor.insertContent('[MOBILEPHONE]');
                        }
                    }, {
                        type: 'menuitem',
                        text: 'Application Reference Code',
                        onAction: function() {
                            editor.insertContent('[REFERENCECODE]');
                        }
                    }, {
                        type: 'menuitem',
                        text: 'Customer New Address',
                        onAction: function() {
                            editor.insertContent('[NEWADDRESS]');
                        }
                    }, {
                        type: 'menuitem',
                        text: 'Customer Old Address',
                        onAction: function() {
                            editor.insertContent('[OLDADDRESS]');
                        }
                    }, {
                        type: 'menuitem',
                        text: 'Customer Portal Link',
                        onAction: function() {
                            editor.insertContent('[CUSTOMERPORTALTINYURL]');
                        }
                    }, {
                        type: 'menuitem',
                        text: 'Customer Portal (V2) Link',
                        onAction: function() {
                            editor.insertContent('[CUSTOMERPORTALV2TINYURL]');
                        }
                    }, {
                        type: 'menuitem',
                        text: 'Workspace Name',
                        onAction: function() {
                            editor.insertContent('[PARTNERNAME]');
                        }
                    }, {
                        type: 'menuitem',
                        text: 'Workspace Address',
                        onAction: function() {
                            editor.insertContent('[PARTNERADDRESS]');
                        }
                    }, {
                        type: 'menuitem',
                        text: 'Workspace Phone',
                        onAction: function() {
                            editor.insertContent('[PARTNERPHONE]');
                        }
                    }, {
                        type: 'menuitem',
                        text: 'Workspace Website',
                        onAction: function() {
                            editor.insertContent('[PARTNERWEBSITE]');
                        }
                    }, {
                        type: 'menuitem',
                        text: 'Connections Brand Name',
                        onAction: function() {
                            editor.insertContent('[PORTALNAME]');
                        }
                    }, {
                        type: 'menuitem',
                        text: 'Connections Hotline',
                        onAction: function() {
                            editor.insertContent('[PARTNERHOTLINE]');
                        }
                    }, {
                        type: 'menuitem',
                        text: 'Move In Date',
                        onAction: function() {
                            editor.insertContent('[MOVEINDATE]');
                        }
                    }, {
                        type: 'menuitem',
                        text: 'Set Callback Link',
                        onAction: function() {
                            editor.insertContent('[SETCALLBACKLINK]');
                        }
                    }, {
                        type: 'menuitem',
                        text: 'Referring Agent',
                        onAction: function() {
                            editor.insertContent('[AGENTFULLNAME]');
                        }
                    }, {
                        type: 'menuitem',
                        text: 'Referring Agent Email',
                        onAction: function() {
                            editor.insertContent('[AGENTEMAIL]');
                        }
                    }, {
                        type: 'menuitem',
                        text: 'Workspace Admin Email',
                        onAction: function() {
                            editor.insertContent('[CAMPAIGNADMINEMAIL]');
                        }
                    }];

                    callback(items);
                }
            });
        }
    });
}