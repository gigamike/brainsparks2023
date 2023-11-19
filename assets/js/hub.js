$(document).ready(function() {

    $.ajaxPrefilter(function(options, origOptions, jqXHR) {
        if (isAjaxRequestPost(options, origOptions)) {
            var da = origOptions.data;
            if (typeof FormData !== 'undefined' && da instanceof FormData) {
                options.data.append('csrfmhub', $('#csrfheaderid').val());
            } else {
                if (typeof options.data === 'undefined') {
                    options.data = "&csrfmhub=" + $('#csrfheaderid').val();
                } else if (options.data.indexOf('csrfmhub') === -1) {
                    options.data += "&csrfmhub=" + $('#csrfheaderid').val(); //$.param($.extend({}, origOptions.data, { csrfmhub: $('#csrfheaderid').val() }));
                }
            }
        }
    });

    /*
     *
     * NO AJAX CACHING
     *
     */
    $.ajaxSetup({ cache: false });


    /*
     *
     * select2
     *
     */
    $.fn.select2.defaults.set("theme", "bootstrap");


    /*
     *
     * TOOLTIP
     *
     */
    $('[data-toggle="tooltip"]').tooltip();

    if ($("body").hasClass("mini-navbar")) {
        $('#side-menu [data-toggle="tooltip"]').tooltip('enable');
    } else {
        $('#side-menu [data-toggle="tooltip"]').tooltip('disable');
    }

    /*
     *
     * TOASTER NOTIFICATIONS
     *
     */
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "onclick": null,
        "showDuration": "400",
        "hideDuration": "1000",
        "timeOut": "7000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };


    /*
     *
     * KB CHECKBOX
     *
     */
    $('.i-checks-kb').iCheck({
        checkboxClass: 'icheckbox_square-blue',
        radioClass: 'iradio_square-blue'
    });

    $('.i-checks-kb').on('ifChanged', function(event) {
        var is_checked = $("#" + event.target.id).is(":checked") ? "1" : "0";

        if ($("#" + event.target.id).is(":checked")) {
            is_checked = 1;
            $("#div_" + event.target.id + ' .qs-text').addClass('todo-completed');
        } else {
            is_checked = 0;
            $("#div_" + event.target.id + ' .qs-text').removeClass('todo-completed');
        }

        $.ajax({
            type: "POST",
            url: baseUrl + "login/ajax-set-hub-user-settings",
            data: {
                'key': event.target.id,
                'val': is_checked
            }
        });



    });

    /*
     *
     * FORM VALIDATION BY JQUERY VALIDATOR
     *
     */
    $.validator.addMethod("exactDigitLength", function(value, element, param) {
        return this.optional(element) || value.length === param;
    }, "Please enter exactly {0} digits");

    $.validator.addMethod("exactCharacterLength", function(value, element, param) {
        return this.optional(element) || value.length === param;
    }, "Please enter exactly {0} characters");

    $.validator.addMethod('minStrict', function(value, el, param) {
        return value > param;
    }, 'Must be greater than {0}');

    $.validator.addMethod("alphanumeric", function(value, element) {
        return this.optional(element) || /^[a-z0-9\\-]+$/i.test(value);
    }, "Must contain only letters or numbers");

    $.validator.addMethod("checkDateFormat", function(value, element) {
        /**
         * CODE BRANCHING HERE - DATE FORMAT
         */
        if (baseDateFormat === "mm/dd/yyyy") {
            return value === "" || value === "  /  /    " || value === "__/__/____" || value.match(/^(0?[1-9]|1[0-2])[.,/ -](0?[1-9]|[12][0-9]|3[0-1])[.,/ -](19|20)?\d{2}$/);
        } else {
            //default format is dd/mm/yyyy
            return value === "" || value === "  /  /    " || value === "__/__/____" || value.match(/^(0?[1-9]|[12][0-9]|3[0-1])[.,/ -](0?[1-9]|1[0-2])[.,/ -](19|20)?\d{2}$/);
        }
    }, "Invalid date format (" + baseDateFormat + ")");

    $.validator.addMethod("checkDOB", function(value, element) {
        if (value === "" || value === "  /  /    " || value === "__/__/____") {
            return true;
        }

        var dateParts = value.split("/");
        var currdate = new Date();
        var setDate = new Date();

        /**
         * CODE BRANCHING HERE - DATE FORMAT
         */
        if (baseDateFormat === "mm/dd/yyyy") {
            setDate.setFullYear(parseInt(dateParts[2]) + 18, (dateParts[0] - 1), dateParts[1]);
        } else {
            //default format is dd/mm/yyyy
            setDate.setFullYear(parseInt(dateParts[2]) + 18, (dateParts[1] - 1), dateParts[0]);
        }

        return ((currdate - setDate) >= 0) ? true : false;
    }, "Must be at least 18 years of age");

    $.validator.addMethod("checkDateValid", function(value, element) {
        if (value === "" || value === "  /  /    " || value === "__/__/____") {
            return true;
        }

        var dateParts = value.split("/");
        var dayobj;

        /**
         * CODE BRANCHING HERE - DATE FORMAT
         */
        if (baseDateFormat === "mm/dd/yyyy") {
            dayobj = new Date(dateParts[2], parseInt(dateParts[0]) - 1, dateParts[1]);
            return ((dayobj.getMonth() + 1 != parseInt(dateParts[0])) || (dayobj.getDate() != parseInt(dateParts[1])) || (dayobj.getFullYear() != parseInt(dateParts[2]))) ? false : true;
        } else {
            //default format is dd/mm/yyyy
            dayobj = new Date(dateParts[2], parseInt(dateParts[1]) - 1, dateParts[0]);
            return ((dayobj.getMonth() + 1 != parseInt(dateParts[1])) || (dayobj.getDate() != parseInt(dateParts[0])) || (dayobj.getFullYear() != parseInt(dateParts[2]))) ? false : true;
        }
    }, "Invalid date");

    $.validator.addMethod("checkDateFuture", function(value, element) {
        if (value === "" || value === "  /  /    " || value === "__/__/____") {
            return true;
        }

        var today = new Date();
        var dateParts = value.split("/");
        var dayobj;

        /**
         * CODE BRANCHING HERE - DATE FORMAT
         */
        if (baseDateFormat === "mm/dd/yyyy") {
            dayobj = new Date(dateParts[2], parseInt(dateParts[0]) - 1, dateParts[1]);
        } else {
            //default format is dd/mm/yyyy
            dayobj = new Date(dateParts[2], parseInt(dateParts[1]) - 1, dateParts[0]);
        }

        return (today >= dayobj) ? false : true;
    }, "Invalid date. Date must be in the future.");

    $.extend($.validator.messages, { required: "Required", email: "Invalid Email", number: "Invalid number", min: "Minimum value of {0}" });

    //adding rule to not allow "anything@something" which is valid per html5 email address
    $.validator.methods.email = function(value, element) {
        return this.optional(element) || /^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/.test(value);
    };

    // Minimalize menu
    // this is already defined in js/inspinia.js
    $('.navbar-minimalize').click(function() {
        if ($("body").hasClass("mini-navbar")) {
            $('#side-menu [data-toggle="tooltip"]').tooltip('disable');
        } else {
            $('#side-menu [data-toggle="tooltip"]').tooltip('enable');
        }

        //save navbar state (if minimized or not)
        $.ajax({
            type: "POST",
            url: baseUrl + "login/ajax-set-global-display-options",
            data: { 'key': 'main_menu_collapsed', 'val': ($("body").hasClass("mini-navbar") ? "" : "mini-navbar"), 'csrfmhub': $('#csrfheaderid').val() }
        });
    });

    // Open close right sidebar - additional step
    // this is already defined in js/inspinia.js
    $('.right-sidebar-toggle').on('click', function() {
        $('[data-toggle="tooltip"]').tooltip('hide');
    });


    //ACCOUNT SELECTOR
    if ($("#accountSelectorRole").length > 0) {
        $("#accountSelectorRole").select2();
    }

    if ($("#accountSelectorTarget").length > 0) {
        $("#accountSelectorTarget").select2();
    }


    $('.goHomeLink').click(function() {
        $.ajax({
            type: "POST",
            url: baseUrl + "login/ajax-go-home",
            success: function(jObj) {
                window.location.href = jObj.redirect_url;
            }
        });
    });

    $('.btn-kb-field-toggle').click(function(e) {
        e.preventDefault();
        var attr = $(this).attr('aria-describedby');
        if (typeof attr !== 'undefined' && attr !== false) {
            $(this).popover('show');
        }
    });
    $('#wrapper').resize(reposition_kb_field_explainers);
    $(window).resize(function() {
        hide_kb_field_explainers();
    });

    $('#secondlevelmenu_1, #secondlevelmenu_3, #secondlevelmenu_5').metisMenu();


    $('#task-guide-checklist .panel-collapse').on('show.bs.collapse', function(e) {
        $(this).siblings('.panel-heading').addClass('active');

        var iframe_url = $(e.target).data('target-iframe-url');
        var iframe_placeholder_id = $(e.target).data('id');
        var iframe_placeholder = '#task-guide-iframe-placeholder-' + iframe_placeholder_id;
        $(iframe_placeholder).html('<iframe width="100%" style="border:none;height: 50vh;" src="' + iframe_url + '"></iframe>');

    });

    $('#task-guide-checklist .panel-collapse').on('hide.bs.collapse', function() {
        $(this).siblings('.panel-heading').removeClass('active');
    });

    /* $('.task-guide-steps-body').slimScroll({
         height: '50%',
         railOpacity: 1,
         wheelStep: 10
     });
     */
    // check_if_amazon_connect_loggedin();
});

function check_if_amazon_connect_loggedin() {
    if (typeof amazonConnectCCPURL !== 'undefined') {
        $.ajax({
            type: "POST",
            url: baseUrl + "amazon-connect/ajax-is-login",
            success: function(jObj) {
                if (!jObj.successful) {
                    $('#amazonConnectLoginModal').modal({
                        backdrop: 'static',
                        keyboard: false
                    })
                    $("#amazonConnectLoginModal").modal('show');
                }
            }
        });

    }
}

function reset_sidebar() {
    $('[data-toggle="tooltip"]').tooltip('hide');

    if (!$('#right-sidebar').hasClass('sidebar-open')) {
        $('#right-sidebar').toggleClass('sidebar-open');
    }

    $('#right-sidebar .sidebar-content').html('');
}

function close_sidebar() {
    $('[data-toggle="tooltip"]').tooltip('hide');

    if ($('#right-sidebar').hasClass('sidebar-open')) {
        $('#right-sidebar').toggleClass('sidebar-open');
        $('#right-sidebar .sidebar-content').html('');
    }
}

function toggle_accounts_menu() {
    close_sidebar();

    if ($("#accountSelectorDiv").css('display') === "none") {
        $("#accountSelectorDiv").removeClass('animated fadeInDown').addClass('animated fadeInDown').css('display', 'block');
        //$("#accountSelectorMenuIcon").removeClass('fa-caret-right').addClass('fa-caret-down');
        //$("#accountSelectorMenuTitle").html('<i>Select Account</i>');
    } else {
        $("#accountSelectorDiv").css('display', 'none');
        //$("#accountSelectorMenuIcon").removeClass('fa-caret-down').addClass('fa-caret-right');
        //$("#accountSelectorMenuTitle").html($('#accountSelectorTarget option:selected').text());
    }
}

function close_accounts_menu() {
    if ($("#accountSelectorDiv").length > 0 && $("#accountSelectorDiv").css('display') !== "none") {
        $("#accountSelectorDiv").css('display', 'none');
        //$("#accountSelectorMenuIcon").removeClass('fa-caret-down').addClass('fa-caret-right');
        $("#accountSelectorMenuTitle").html($('#accountSelectorTarget option:selected').text());
    }
}

function account_selector_role_changed() {
    $.ajax({
        type: "POST",
        url: baseUrl + "login/ajax-update-account-selector-target-div",
        data: { 'opt1': $("#accountSelectorRole option:selected").val() },
        beforeSend: function() {
            $("#accountSelectorTargetDiv").html('<i class="m-t-sm fa fa-refresh fa-spin"></i>');
        },
        success: function(jObj) {
            $("#accountSelectorTargetDiv").html(jObj.html);
            $("#accountSelectorTarget").select2();

            //set if theres only 1 option (+1 because of disabled "placeholder")
            if ($("#accountSelectorTarget option").length === 2) {
                $("#accountSelectorTarget").val($("#accountSelectorTarget option:last").val()).select2().trigger("change");
            }
        }
    });

}

function account_selector_target_changed() {
    $.ajax({
        type: "POST",
        url: baseUrl + "login/ajax-update-target-account",
        data: { 'opt1': $("#accountSelectorRole option:selected").val(), 'opt2': $("#accountSelectorTarget option:selected").val() },
        success: function(jObj) {
            window.location.href = jObj.redirect_url;
        }
    });
}

/**
 *
 * Knowledge base
 *
 */

$(document).ready(function() {
    $('.kb-alert').on('close.bs.alert', function(e) {
        $(this).blur();
        e.preventDefault();
        toggle_kb_content($(this).attr('attr-section'));
    });

});


function toggle_kb_content(id) {

    if ($("#kbExplainer-" + id).css('display') == "none") {
        $("#kbExplainer-" + id).css('display', 'block');
        $("#kbToggler-" + id).css('display', 'none');


        $.ajax({
            type: "POST",
            url: baseUrl + "login/ajax-set-hub-user-settings",
            data: {
                'key': id,
                'val': "1"
            }
        });
    } else {
        $("#kbExplainer-" + id).css('display', 'none');
        $("#kbToggler-" + id).css('display', 'inline');

        $.ajax({
            type: "POST",
            url: baseUrl + "login/ajax-set-hub-user-settings",
            data: {
                'key': id,
                'val': "0"
            }
        });
    }

}


function show_quickstart() {
    $("#quickStartModal").modal("show");
}

function show_quickstart_embed_content(id) {
    $("#quickStartModal").modal("hide");
    show_embed_content(id);
}

function show_quickstart_embed_video(id) {
    $("#quickStartModal").modal("hide");
    show_embed_video(id);
}

function show_embed_content(id) {
    $.ajax({
        type: "POST",
        url: baseUrl + "knowledgebase/ajax-load-content-modal",
        data: { 'id': id },
        beforeSend: function() {
            $("#spinnerModal").modal('show');
        },
        success: function(jObj) {
            $("#kbEmbedModalBody").html(jObj.html_str);

            //call function
            window['createIframe' + jObj.random_string]();


            $("#spinnerModal").modal('hide');
            $("#kbEmbedModal").modal("show");
        }
    });
}

function show_embed_video(id) {
    $.ajax({
        type: "POST",
        url: baseUrl + "knowledgebase/ajax-load-video-modal",
        data: { 'id': id },
        beforeSend: function() {
            $("#spinnerModal").modal('show');
        },
        success: function(jObj) {
            $("#kbEmbedModalBody").html(jObj.html_str);

            //call function
            window['createIframe' + jObj.random_string]();


            $("#spinnerModal").modal('hide');
            $("#kbEmbedModal").modal("show");
        }
    });
}

function show_embed_code_subsection(id) {
    $.ajax({
        type: "POST",
        url: baseUrl + "knowledgebase/ajax-load-subsection-modal",
        data: { 'id': id },
        beforeSend: function() {
            $("#spinnerModal").modal('show');
        },
        success: function(jObj) {
            $("#kbEmbedModalBody").html(jObj.html_str);

            //call function
            window['createIframe' + jObj.random_string]();


            $("#spinnerModal").modal('hide');
            $("#kbEmbedModal").modal("show");
        }
    });
}

$('#kbEmbedModal').on('hide.bs.modal', function(e) {
    $("#kbEmbedModalBody").html('');
});



/**
 *
 * Addons Subscriptions
 *
 */

function confirm_addons_subscription(module, action, redirect_uri) {
    $.ajax({
        type: "POST",
        url: baseUrl + "sass-subscriptions/ajax-check-subscription",
        data: { 'module': module, 'action': action },
        beforeSend: function() {},
        success: function(jObj) {
            if (!jObj.subscribed) {
                if (jObj.show_modal) {
                    $("#addOnsSubscriptionAlertModalContainer").html(jObj.modal_html);
                    $("#addOnsSubscriptionAlertModal").modal("show");

                    if (redirect_uri !== null && redirect_uri !== "") {
                        //define listener
                        $('#addOnsSubscriptionAlertModal').on('hidden.bs.modal', function(e) {
                            window.location.href = baseUrl + redirect_uri;
                        });
                    }
                } else {
                    swal({
                        title: "",
                        text: jObj.error_str,
                        type: "error",
                        allowEscapeKey: false
                    }, function() {
                        if (redirect_uri !== null && redirect_uri !== "") {
                            window.location.href = baseUrl + redirect_uri;
                        }
                    });
                }
            }
        }
    });
}

function confirm_agent_addons_subscription(module, action, redirect_uri) {
    $.ajax({
        type: "POST",
        url: baseUrl + "sass-subscriptions/ajax-check-subscription-agent",
        data: { 'module': module, 'action': action },
        beforeSend: function() {},
        success: function(jObj) {
            if (!jObj.subscribed) {
                if (jObj.show_modal) {
                    $("#addOnsSubscriptionAlertModalContainer").html(jObj.modal_html);
                    $("#addOnsSubscriptionAlertModal").modal("show");

                    if (redirect_uri !== null && redirect_uri !== "") {
                        //define listener
                        $('#addOnsSubscriptionAlertModal').on('hidden.bs.modal', function(e) {
                            window.location.href = baseUrl + redirect_uri;
                        });
                    }
                } else {
                    swal({
                        title: "",
                        text: jObj.error_str,
                        type: "error",
                        allowEscapeKey: false
                    }, function() {
                        if (redirect_uri !== null && redirect_uri !== "") {
                            window.location.href = baseUrl + redirect_uri;
                        }
                    });
                }
            }
        }
    });
}


function gotoContactAM() {

    $.ajax({
        type: "POST",
        url: baseUrl + "sass-subscriptions/ajax-check-subscription",
        data: { 'module': "premium_support", "action": "" },
        beforeSend: function() {},
        success: function(jObj) {
            if (jObj.subscribed) {
                window.location.href = $("#gotoContactAMLink").attr('href');
            } else {
                if (jObj.show_modal) {
                    $("#addOnsSubscriptionAlertModalContainer").html(jObj.modal_html);
                    $("#addOnsSubscriptionAlertModal").modal("show");
                } else {
                    swal({
                        title: "",
                        text: jObj.error_str,
                        type: "error"
                    }, function() {});
                }
            }
        }
    });

    return false;
}

// Activate WOW.js plugin for animation on scroll
new WOW().init();

function scroll_to_element(element) {
    if (element === null) {
        $('html, body').animate({ scrollTop: 0 });
    } else {
        $('html, body').animate({ scrollTop: element.offset().top - 100 });
    }
}

//helper function
function cleanup_html_input(code) {
    if (code === "" || code === "<p><br></p>") {
        return "";
    } else {
        return code.replace('/(<p><br></p>)+/', '');
    }
}


function hide_kb_field_explainers() {
    $('.btn-kb-field-toggle').each(function() {
        $(this).popover('hide');
    });
}

function reposition_kb_field_explainers() {
    $('.btn-kb-field-toggle').each(function() {
        var attr = $(this).attr('aria-describedby');
        if (typeof attr !== 'undefined' && attr !== false) {
            $(this).popover('show');
        }
    });
}

function adjustSidebarPanel() {
    setTimeout(function() {
        var heightWithoutNavbar = $("#page-wrapper").height() - 70;
        $(".sidebar-panel").css("min-height", heightWithoutNavbar + "px");
    }, 1000);
}