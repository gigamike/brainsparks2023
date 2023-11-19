
var global_spinner = "fa fa-spinner fa-spin";
var global_pre_spinner = "";

function show_spinner(item) {
    global_pre_spinner = $("i", item).attr("class");
    $("i", item).attr("class", global_spinner);
}

function hide_spinner(item) {
    $("i", item).attr("class", global_pre_spinner);
}


function toggle_cc_bcc_fields(input_id, current_status) {
    var set_cc_bbc_display;
    if (current_status == true) {
        set_cc_bbc_display = 'block';
        $("#" + input_id).removeAttr('disabled');

    } else {
        set_cc_bbc_display = 'none';
        $("#" + input_id).attr('disabled', 'disabled');
    }
    $("#" + input_id + "Div").css('display', set_cc_bbc_display);
}


function detect_cc_bcc_events() {
    var input_id_cc = 'ccUserAssignedFilter';
    var current_status_cc = false;
    var input_id_bcc = 'bccUserAssignedFilter';
    var current_status_bcc = false;
    toggle_cc_bcc_fields(input_id_cc, current_status_cc);
    toggle_cc_bcc_fields(input_id_bcc, current_status_bcc);
    $('#ccLink').click(function () {
        current_status_cc = true;
        toggle_cc_bcc_fields(input_id_cc, current_status_cc);
        $(this).prop("disabled", true);
    });
    $('#bccLink').click(function () {
        current_status_bcc = true;
        toggle_cc_bcc_fields(input_id_bcc, current_status_bcc);
        $(this).prop("disabled", true);
    });
}


function isValidEmailAddress(emailAddress) {
    var pattern = /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i;
    return pattern.test(emailAddress);
}

// check if its a valid email address
function user_assigned_filter_selection(get_selected_text) {
    var isValidEmail;
    var get_selected_email_text;
    if (get_selected_text.indexOf("(") >= 0 && get_selected_text.indexOf(")") >= 0) {
        //check the brackets and parse the email address
        var email_parse_result = get_selected_text.match(/\((.*)\)/);
        get_selected_email_text = email_parse_result[1];
    } else {
        get_selected_email_text = get_selected_text;
    }

    if (get_selected_email_text.length > 0) {
        isValidEmail = isValidEmailAddress(get_selected_email_text);
        if (isValidEmail === true) {
            return true;
        }
    }
    return false;
}