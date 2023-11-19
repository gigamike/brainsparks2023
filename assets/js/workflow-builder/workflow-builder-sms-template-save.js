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
        template: { required: true }
    };

    formValidator = $("#SMSTemplateForm").validate({
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

        $("#SMSTemplateForm")[0].reset();
    });

    $('#saveBtn').click(function() {

        $(this).blur();

        //validate form
        if (!$("#SMSTemplateForm").valid()) {
            return;
        }

        //save
        $.ajax({
            type: 'POST',
            url: baseUrl + 'workflow-builder/ajax-sms-template-save',
            data: $("#SMSTemplateForm").serialize(),
            beforeSend: function() {
                $("#spinnerModal").modal('show');
            },
            success: function(jObj) {
                $("#spinnerModal").modal("hide");
                if (jObj.successful) {
                    swal({
                        title: "",
                        text: "SMS template saved!",
                        type: "success",
                        confirmButtonColor: "#2C83FF",
                        confirmButtonText: "Close"
                    }, function() {
                        window.location.href = baseUrl + "workflow-builder/sms-template";
                    });
                } else {
                    swal('', jObj.error, "error");
                }
            }
        });
    });

    displaySMSMessageCounter();

    $('#template').keyup(function() {
        var text_length = $('#template').val().length;
        var text_str = parseInt(text_length) > 0 ? text_length + ' characters' : '';
        $('#SMSMessageCounter').html(text_str);
    });

    $('#token').change(function() {
        var token = $(this).val();
        $('#template').val($('#template').val() + ' ' + token);

        displaySMSMessageCounter();

        $(this).val('');
    });
});

function init_load() {}

function init_load_add() {
    confirm_addons_subscription('custom_workflow_sms_templates', 'add', 'workflow-builder/sms-template');
    init_load();
}

function init_load_update() {
    //we only want to know if custom_workflow_email_templates is on
    //we dont care whether theres still seats left
    confirm_addons_subscription('custom_workflow_sms_templates', '', 'workflow-builder/sms-template');
    init_load();
}


function displaySMSMessageCounter() {
    var text_length = $('#template').val().length;
    var text_str = parseInt(text_length) > 0 ? text_length + ' characters' : '';
    $('#SMSMessageCounter').html(text_str);
}