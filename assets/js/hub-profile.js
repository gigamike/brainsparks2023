var formValidator = null;
var userProfileFormValidator = null;
var userProfileSecurityFormValidator = null;
var userProfileEmailSettingsFormValidator = null;
var userProfileRewardsFormValidator = null;
var userProfileUnsubscribeFormValidator = null;
var totalMultiEmailsAllowed = 2;


function slugify(string) {
    return string
        .toString()
        .trim()
        .toLowerCase()
        .replace(/\s+/g, "-")
        .replace(/[^\w\-]+/g, "")
        .replace(/\-\-+/g, "-")
        .replace(/^-+/, "")
        .replace(/-+$/, "");
}

$(document).ready(function() {
    //prevents caching
    $.ajaxSetup({ cache: false });


    //prevent form submission by ENTER
    $(window).keydown(function(event) {
        if (event.keyCode === 13) {
            event.preventDefault();
            return false;
        }
    });

    //copy to clipboard
    new Clipboard('.copy-to-clipboard', {
        text: function(trigger) {
            return $($(trigger).attr('data-clipboard-target')).val();
        }
    });

    //icheck
    $('.i-checks').iCheck({
        checkboxClass: 'icheckbox_square-blue',
        radioClass: 'iradio_square-blue'
    });

    /**
     * CODE BRANCHING HERE - COUNTRY
     *      AU
     *      NZ
     *      US
     *      UK
     */
    var rules_options;

    if (baseCountry === "AU") {
        rules_options = {
            mobile_phone: { digits: true, exactDigitLength: 10 },
            office_phone: { digits: true, exactDigitLength: 10 },
            office_extension: { digits: true }
        };
    } else if (baseCountry === "NZ") {
        rules_options = {
            mobile_phone: { digits: true, minlength: 9, maxlength: 11 },
            office_phone: { digits: true, minlength: 9, maxlength: 11 },
            office_extension: { digits: true }
        };
    } else if (baseCountry === "US") {
        rules_options = {
            mobile_phone: { digits: true, exactDigitLength: 10 },
            office_phone: { digits: true, exactDigitLength: 10 },
            office_extension: { digits: true }
        };
    } else if (baseCountry === "UK") {
        rules_options = {
            mobile_phone: { digits: true, exactDigitLength: 11 },
            office_phone: { digits: true, exactDigitLength: 11 },
            office_extension: { digits: true }
        };
    }


    rules_options.microsite_id = {
        remote: {
            url: baseUrl + "partner/ajax-validator-agent-micrositeid-used",
            type: "POST",
            data: {
                'microsite_id': function() {
                    return $("input[name=microsite_id]").val();
                },
            }
        }
    };

    rules_options.user_email = {
        remote: {
            url: baseUrl + "partner/ajax-validator-email-blacklisted",
            type: "POST",
            data: {
                'email': function() {
                    return $("#user_email").val();
                },
            }
        }
    };

    userProfileFormValidator = $("#userProfileForm").validate({
        rules: rules_options,
        messages: {
            "microsite_id": {
                "remote": $.validator.format("Sorry, but \"{0}\" is already in use")
            },
            "payment_summary_email_cc": {
                "multiemails": "Please enter valid comma seperated email addresses. Maximum " + (parseInt(totalMultiEmailsAllowed) + 1) + " emails allowed."
            }
        },
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

    var rules_options = {
        new_password_confirm: {
            equalTo: "#new_password"
        }
    };

    userProfileSecurityFormValidator = $("#userProfileSecurityForm").validate({
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

    userProfileEmailSettingsFormValidator = $("#userProfileEmailSettingsForm").validate({
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

    $.validator.addMethod(
        "multiemails",
        function(value, element) {
            if (this.optional(element)) // return true on optional element
                return true;
            var emails = value.split(/[;,]+/); // split element by , and ;
            valid = true;
            for (var i in emails) {
                if (i > totalMultiEmailsAllowed) {
                    valid = false;
                } else {
                    value = emails[i];
                    valid = valid &&
                        $.validator.methods.email.call(this, $.trim(value), element);
                }
            }
            return valid;
        },
        $.validator.messages.multiemails
    );


    /**
     * CODE BRANCHING HERE - COUNTRY
     *      AU
     *      NZ
     *      US
     *      UK
     */
    var rules_options;

    if (baseCountry === "AU") {
        rules_options = {
            user_abn: { digits: true, exactDigitLength: 11 },
            bank_acc_no: { digits: true },
            bank_bsb: { digits: true, exactDigitLength: 6 },
            payment_summary_email_cc: { multiemails: true }
        };
    } else if (baseCountry === "NZ") {
        jQuery.validator.addMethod("alphanumeric", function(value, element) {
            return this.optional(element) || /^[0-9-]*$/i.test(value);
        }, "Digits and dashes only");
        rules_options = {
            user_irdn: { digits: true, minlength: 8, maxlength: 9 },
            bank_acc_no: { alphanumeric: true },
            payment_summary_email_cc: { multiemails: true }
        };
    } else if (baseCountry === "US") {
        rules_options = {
            bank_acc_no: { digits: true },
            bank_routing_number: { digits: true },
            payment_summary_email_cc: { multiemails: true }
        };
    } else if (baseCountry === "UK") {
        rules_options = {
            bank_acc_no: { digits: true },
            bank_sort_code: { digits: true, exactDigitLength: 6 },
            payment_summary_email_cc: { multiemails: true }
        };
    }

    userProfileRewardsFormValidator = $("#userProfileRewardsForm").validate({
        rules: rules_options,
        messages: {
            "payment_summary_email_cc": {
                "multiemails": "Please enter valid comma seperated email addresses. Maximum " + (parseInt(totalMultiEmailsAllowed) + 1) + " emails allowed."
            }
        },
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

    userProfileUnsubscribeFormValidator = $("#userProfileUnsubscribeForm").validate({
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

    //typeahead
    var timeout;
    $("#prepaid_mastercard_debit_address").typeahead({
        minLength: 4,
        autoSelect: false,
        source: function(query, process) {
            if (timeout) {
                clearTimeout(timeout);
            }
            timeout = setTimeout(function() {
                return ajax_complete_address(query, process);
            }, 400);
        },
        matcher: function(item) {
            return true;
        },
        highlighter: Object,
        displayText: function(item) {
            return item.id === "google" ? '<img src="' + baseUrl + 'assets/img/powered-by-google/desktop/powered_by_google_on_white.png">' : item.name;
        },
        afterSelect: function(item) {
            $("#prepaid_mastercard_debit_address").val(item.name).change();
        }
    });

    getTinymce();

    // Payment method
    $('input[name=payment_method]').change(function() {
        //hide all sub options
        $(".payment-method-options").css('display', 'none');
        $(".payment-method-options :input").prop('disabled', true).prop('required', false);


        //show selected method options
        $("#userPaymentMethodDiv-" + $(this).val()).css('display', 'block');
        $("#userPaymentMethodDiv-" + $(this).val() + " :input").prop('disabled', false).prop('required', true);

        if (parseInt($(this).val()) === 0) {
            $("#payoutSettingsDiv").css('display', 'none');
            $('#auto_payout_enabled').iCheck('disable');
        } else {
            $("#payoutSettingsDiv").css('display', 'block');
            $('#auto_payout_enabled').iCheck('enable');
        }
    });

    //Password
    $('#new_password').keyup(function() {
        $('#password_meter').html(checkStrength($('#new_password').val()));
    });

    $('#saveBtn').click(function() {
        $(this).blur();

        $('#hiddenSubmitBtn').click();

    });

    $('#resetBtn').click(function() {
        $(this).blur();

        $("#userProfileForm")[0].reset();
        $("#userProfileForm .form-group").removeClass('has-error');
        $("#userProfileForm .help-block").remove();

        //hide all sub options
        $(".payment-method-options").css('display', 'none');
        $(".payment-method-options :input").prop('disabled', true);
        //show selected method options
        $("#userPaymentMethodDiv-" + $('input[name=payment_method]:checked').val()).css('display', 'block');
        $("#userPaymentMethodDiv-" + $('input[name=payment_method]:checked').val() + " :input").prop('disabled', false);

    });

    $('#saveProfileBtn').click(function() {
        $(this).blur();

        if (!$("#userProfileForm").valid()) {
            return;
        }

        var data = new FormData($("#userProfileForm")[0]);

        $.ajax({
            type: "POST",
            url: baseUrl + "profile/ajax-profile-save",
            data: data,
            processData: false,
            contentType: false,
            beforeSend: function() {
                $("#spinnerModal").modal('show');
            },
            success: function(jObj) {
                $("#spinnerModal").modal("hide");
                if (jObj.successful) {
                    swal('', "Profile saved!", "success");
                } else {
                    swal('', jObj.error, "error");
                }
            }
        });
    });

    $('#saveSecurityBtn').click(function() {
        $(this).blur();

        if (!$("#userProfileSecurityForm").valid()) {
            return;
        }

        $.ajax({
            type: "POST",
            url: baseUrl + "profile/ajax-profile-security-save",
            data: $("#userProfileSecurityForm").serialize(),
            beforeSend: function() {
                $("#spinnerModal").modal('show');
            },
            success: function(jObj) {
                $("#spinnerModal").modal("hide");
                if (jObj.successful) {
                    swal('', "Profile saved!", "success");
                } else {
                    swal('', jObj.error, "error");
                }
            }
        });
    });

    $('#saveRewardsBtn').click(function() {
        $(this).blur();

        if (!$("#userProfileRewardsForm").valid()) {
            return;
        }

        $.ajax({
            type: "POST",
            url: baseUrl + "profile/ajax-profile-rewards-save",
            data: $("#userProfileRewardsForm").serialize(),
            beforeSend: function() {
                $("#spinnerModal").modal('show');
            },
            success: function(jObj) {
                $("#spinnerModal").modal("hide");
                if (jObj.successful) {
                    swal('', "Profile saved!", "success");
                } else {
                    swal('', jObj.error, "error");
                }
            }
        });
    });


    $('#saveEmailSettingBtn').click(function() {
        $(this).blur();

        if (!$("#userProfileEmailSettingsForm").valid()) {
            return;
        }

        $.ajax({
            type: "POST",
            url: baseUrl + "profile/ajax-profile-email-settings-save",
            data: $("#userProfileEmailSettingsForm").serialize(),
            beforeSend: function() {
                $("#spinnerModal").modal('show');
            },
            success: function(jObj) {
                $("#spinnerModal").modal("hide");
                if (jObj.successful) {
                    swal('', "Profile saved!", "success");
                } else {
                    swal('', jObj.error, "error");
                }
            }
        });
    });

    $('#saveUnsubscribeBtn').click(function() {
        $(this).blur();

        if (!$("#userProfileUnsubscribeForm").valid()) {
            return;
        }

        $.ajax({
            type: "POST",
            url: baseUrl + "profile/ajax-profile-unsubscribe-save",
            data: $("#userProfileUnsubscribeForm").serialize(),
            beforeSend: function() {
                $("#spinnerModal").modal('show');
            },
            success: function(jObj) {
                $("#spinnerModal").modal("hide");
                if (jObj.successful) {
                    swal('', "Profile saved!", "success");
                } else {
                    swal('', jObj.error, "error");
                }
            }
        });
    });

    $('#resetProfileBtn').click(function() {
        $(this).blur();

        $("#userProfileForm")[0].reset();
        $("#userProfileForm .form-group").removeClass('has-error');
        $("#userProfileForm .help-block").remove();

    });

    $('#resetSecurityBtn').click(function() {
        $(this).blur();

        $("#userProfileSecurityForm")[0].reset();
        $("#userProfileSecurityForm .form-group").removeClass('has-error');
        $("#userProfileSecurityForm .help-block").remove();

    });

    $('#resetRewardsBtn').click(function() {
        $(this).blur();

        $("#userProfileRewardsForm")[0].reset();
        $("#userProfileRewardsForm .form-group").removeClass('has-error');
        $("#userProfileRewardsForm .help-block").remove();

        //hide all sub options
        $(".payment-method-options").css('display', 'none');
        $(".payment-method-options :input").prop('disabled', true);
        //show selected method options
        $("#userPaymentMethodDiv-" + $('input[name=payment_method]:checked').val()).css('display', 'block');
        $("#userPaymentMethodDiv-" + $('input[name=payment_method]:checked').val() + " :input").prop('disabled', false);

    });

    $('#resetEmailSettingsBtn').click(function() {
        $(this).blur();

        $("#userProfileEmailSettingsForm")[0].reset();
        $("#userProfileEmailSettingsForm .form-group").removeClass('has-error');
        $("#userProfileEmailSettingsForm .help-block").remove();
    });

    $('#resetUnsubscribeBtn').click(function() {
        $(this).blur();

        $("#userProfileUnsubscribeForm")[0].reset();
        $("#userProfileUnsubscribeForm .form-group").removeClass('has-error');
        $("#userProfileUnsubscribeForm .help-block").remove();

    });


    $('input[name=microsite_id]').blur(function() {
        $(this).val(slugify($(this).val()));
    });

    $('.preferred_phone_number').change(function(e) {
        var elt = $(e.target);
        var val = elt.val();

        if (val == 'office') {

            $('#mobile-phone-section').hide();
            $('#mobile-phone-divider').hide();

            $('#office-phone-section').show();
            $('#office-extension-section').show();
            $('#office-phone-divider').show();
            $('#office-extension-divider').show();

            $('input[name=office_phone]').attr('required', 'required');
            $('input[name=mobile_phone]').removeAttr('required');

        } else if (val == 'mobile') {

            $('#mobile-phone-section').show();
            $('#mobile-phone-divider').show();

            $('#office-phone-section').hide();
            $('#office-extension-section').hide();
            $('#office-phone-divider').hide();
            $('#office-extension-divider').hide();

            $('input[name=office_phone]').removeAttr('required');
            $('input[name=mobile_phone]').attr('required', 'required');
        }
    });

    /** transactions (payments) dataTable start */
    var dtObj_transactions = $('#dtPaymentHistory').dataTable({
        //dom: '<<t><"row"<"col-md-4"l><"col-md-4 text-center"i><"col-md-4"p>>>',
        dom: '<<t><"row m-t-sm"<"col-sm-6"i><"col-sm-6"p>>>',
        responsive: false,
        autoWidth: false,
        processing: true,
        serverSide: true,
        scrollX: true,
        scrollY: true,
        pageLength: 5,
        pagingType: 'full',
        ajax: {
            "url": baseUrl + "agent/ajax-dt-get-payment-history",
            "data": function(d) {
                d.agentID = $('input[name=wallet_agentID]').val();
                d.searchText = $('#dtSearchText_payment').val();
            }
        },
        columns: [
            { "data": "transaction_date" },
            { "data": "transaction_details", "orderable": false },
            { "data": "amount" }
        ],
        order: [
            [$('#wallet_order_col').val(), $('#wallet_order_dir').val()]
        ],
        language: {
            "search": "_INPUT_", //search
            "searchPlaceholder": "Search Records",
            "lengthMenu": "Show _MENU_", //label
            "emptyTable": "None Found.",
            "info": "_START_ to _END_ of _TOTAL_", //label
            "paginate": { "next": "<i class=\"fa fa-angle-right\"></i>", "previous": "<i class=\"fa fa-angle-left\"></i>", "last": "<i class=\"fa fa-angle-double-right\"></i>", "first": "<i class=\"fa fa-angle-double-left\"></i>" } //pagination
        }
    });

    //search
    $('#dtSearchText_payment').bind('keyup', function(e) {
        if (e.keyCode === 13) {
            dtObj_transactions.api().ajax.reload();
        }
    });

    $('#dtSearchBtn_payment').click(function() {
        $(this).blur();
        dtObj_transactions.api().ajax.reload();
    });

    $(window).resize(function() {
        dtObj_transactions.fnAdjustColumnSizing();
    });

    $(".navbar-minimalize").click(function() {
        // add delay since inspinia.js adds delay in SmoothlyMenu()
        setTimeout(function() {
            dtObj_transactions.fnAdjustColumnSizing();
        }, 310);
    });

    dtObj_transactions.fnAdjustColumnSizing();

    //event after dt reload
    //to update summary table
    $('#dtPaymentHistory').on('draw.dt', function(e, settings) {
        //console.log(settings.json.totalFeesArr);
        $.each(settings.json.totalAmountArr, function(key, value) {
            $('#' + key + "Div").html(parseFloat(value).toLocaleString('en', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        });


        //load wallet
        $.ajax({
            type: "POST",
            url: baseUrl + "agent/ajax-get-agent-wallet-funds",
            data: { agent: $('input[name=wallet_agentID]').val() },
            success: function(jObj) {
                if (jObj.successful) {

                    $("#remainingPayoutDiv").html(parseFloat(jObj.dataset.remaining_payout).toLocaleString('en', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                    $("#totalPayoutDiv").html(parseFloat(jObj.dataset.total_payout).toLocaleString('en', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

                    //hide request button if no payment method set or no remaining payout
                    if (parseFloat(jObj.dataset.remaining_payout) <= 0.00 || parseInt(jObj.dataset.remaining_payout) === 0) {
                        $("#requestPayoutBtn").css("display", "none");
                    } else {
                        $("#requestPayoutBtn").css("display", "inline");
                    }
                }
            }
        });

    });

    /** transactions dataTable end */


    /** RCTIs dataTable start */
    var dtObj_rcti = $('#dtPaymentRCTI').dataTable({
        //dom: '<<t><"row"<"col-md-4"l><"col-md-4 text-center"i><"col-md-4"p>>>',
        dom: '<<t><"row m-t-sm"<"col-sm-6"i><"col-sm-6"p>>>',
        responsive: false,
        autoWidth: false,
        processing: true,
        serverSide: true,
        scrollX: true,
        scrollY: true,
        pageLength: 5,
        pagingType: 'full',
        ajax: {
            "url": baseUrl + "agent/ajax-dt-get-payment-rcti",
            "data": function(d) {
                d.agentID = $('input[name=wallet_agentID]').val();
                d.partnerID = $('input[name=wallet_partnerID]').val();
                d.searchText = $('#dtSearchText_rcti').val();
            }
        },
        columns: [
            { "data": "receipt_code" },
            { "data": "role", "orderable": false, "searchable": false },
            { "data": "rcti_file", "className": "td-wrapping-line" },
            { "data": "date_added_formatted" },
            { "data": "actions", "orderable": false, "searchable": false }
        ],
        order: [
            [$('#rcti_order_col').val(), $('#rcti_order_dir').val()]
        ],
        language: {
            "search": "_INPUT_", //search
            "searchPlaceholder": "Search Records",
            "lengthMenu": "Show _MENU_", //label
            "emptyTable": "None Found.",
            "info": "_START_ to _END_ of _TOTAL_", //label
            "paginate": { "next": "<i class=\"fa fa-angle-right\"></i>", "previous": "<i class=\"fa fa-angle-left\"></i>", "last": "<i class=\"fa fa-angle-double-right\"></i>", "first": "<i class=\"fa fa-angle-double-left\"></i>" } //pagination
        }
    });

    //search
    $('#dtSearchText_rcti').bind('keyup', function(e) {
        if (e.keyCode === 13) {
            dtObj_rcti.api().ajax.reload();
        }
    });

    $('#dtSearchBtn_rcti').click(function() {
        $(this).blur();
        dtObj_rcti.api().ajax.reload();
    });

    $(window).resize(function() {
        dtObj_rcti.fnAdjustColumnSizing();
    });

    $(".navbar-minimalize").click(function() {
        // add delay since inspinia.js adds delay in SmoothlyMenu()
        setTimeout(function() {
            dtObj_rcti.fnAdjustColumnSizing();
        }, 310);
    });
    dtObj_rcti.fnAdjustColumnSizing();
    /** RCTIs dataTable end */


    /** resize column after a tab is shown */
    $('.nav-tabs').find('a').on('shown.bs.tab', function() {
        dtObj_rcti.fnAdjustColumnSizing();
        dtObj_transactions.fnAdjustColumnSizing();
    });


    $("input[name='subscription_categories[]'][value=2]").click(function() {
        if ($(this).is(":checked")) {
            $("input[name^='email_group_']").prop('checked', false).removeAttr('checked');
        } else if ($(this).is(":not(:checked)")) {
            $("input[name^='email_group_']").prop('checked', true).attr('checked', 'checked');
        }
    });


    $('#requestPayoutBtn').click(function() {
        $.ajax({
            type: "POST",
            url: baseUrl + "profile/ajax-request-wallet-payout",
            dataType: 'json',
            data: {},
            beforeSend: function() {
                $("#spinnerModal").modal('show');
            },
            success: function(jObj) {
                $("#spinnerModal").modal("hide");
                if (jObj.successful) {
                    $("#requestPayoutBtn").prop('disabled', true);
                    swal('', "Request sent!", "success");
                } else {
                    swal('', jObj.error, "error");
                }
            }
        });
    });


});


function ajax_complete_address(query, process) {
    $.ajax({
        type: "POST",
        url: baseUrl + "profile/ajax-complete-address",
        dataType: 'json',
        data: { term: query },
        success: function(data) {
            return process(data);
        }
    });
}

function ajax_parse_address(item) {
    if (item.id === "manualAddress") {
        return;
    }

    $.ajax({
        type: "POST",
        url: baseUrl + "profile/ajax-parse-address",
        dataType: 'json',
        data: { id: item.id, value: item.name },
        success: function(jsonObj) {}
    });
}

function checkStrength(password) {
    var strength = 0;
    if (password.length >= parseInt($("#recommended_length").val())) {
        strength += 25;
    }
    if (password.match(/[a-z]/)) {
        strength += 25;
    }
    if (password.match(/[A-Z]/)) {
        strength += 25;
    }

    if (password.match(/\d/) || password.match(/[^a-zA-Z\d]/)) {
        strength += 25;
    }

    return '<div style="width: ' + strength + '%;" class="progress-bar progress-bar-info"></div>';

}


function init_load() {

    //hide label
    $("#accountSelectorMenuTitle").html('');

    if ($("#action_message_success").length) {
        swal('', $("#action_message_success").val(), "success");
    } else if ($("#action_message_failed").length) {
        swal('', $("#action_message_failed").val(), "error");
    }
}

function getTinymce() {
    $('.tinymce').tinymce({
        contextmenu: false,
        browser_spellcheck: true,
        height: 500,
        menubar: 'edit view insert format table tools',
        plugins: [
            'advlist autolink lists link image charmap print preview anchor',
            'searchreplace visualblocks code fullscreen',
            'insertdatetime media table paste code help wordcount',
            'autoresize codesample directionality emoticons hr legacyoutput nonbreaking pagebreak tabfocus textpattern visualchars imagetools'
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
                        text: 'Agent Fullname',
                        onAction: function() {
                            editor.insertContent('[AGENTFULLNAME]');
                        }
                    }, {
                        type: 'menuitem',
                        text: 'Agent Firstname',
                        onAction: function() {
                            editor.insertContent('[AGENTFIRSTNAME]');
                        }
                    }, {
                        type: 'menuitem',
                        text: 'Agent Email',
                        onAction: function() {
                            editor.insertContent('[AGENTEMAIL]');
                        }
                    }, {
                        type: 'menuitem',
                        text: 'Agent Mobile Phone',
                        onAction: function() {
                            editor.insertContent('[AGENTMOBILEPHONE]');
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
                    }];

                    callback(items);
                }
            });
        }
    });
}

function download_rcti_file($file_uri) {
    $.ajax({
        type: "POST",
        url: baseUrl + "agent/file_exists",
        data: { download_uri: $file_uri },
        success: function(data) {
            var jObj = JSON.parse(data);
            if (jObj.exists) {
                var $form = $("#downloadForm");
                if ($form.length == 0) {
                    $form = $("<form>").attr({ "id": "downloadForm", "method": "POST", "action": baseUrl + "agent/force-download" }).hide();
                    $("body").append($form);
                }
                $form.find("input").remove();
                var args = { download_uri: $file_uri, csrfmhub: $('#csrfheaderid').val() };
                for (var field in args) {
                    $form.append($("<input>").attr({ "value": args[field], "name": field }));
                }
                $form.submit();
            }

        }
    });
}