var formValidator = null;

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

    var rules_options = {
        subject: { required: true },
        description: { required: true },
        priority: { required: true }
    };

    formValidator = $("#userSupportForm").validate({
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

        formValidator.resetForm();
        $("#userSupportForm .form-group").removeClass('has-error');
        $("#userSupportForm")[0].reset();
    });

    //save agent
    $('#saveBtn').click(function() {
        $(this).blur();

        //validate form
        if (!$("#userSupportForm").valid()) {
            return;
        }

        //save
        $.ajax({
            type: 'POST',
            url: baseUrl + 'support/ajax-support-save',
            data: $("#userSupportForm").serialize(),
            beforeSend: function() {
                $("#spinnerModal").modal('show');
            },
            success: function(jObj) {
                $("#spinnerModal").modal("hide");
                if (jObj.successful) {
                    swal({
                        title: "",
                        text: jObj.message,
                        type: "success",
                        confirmButtonColor: "#2C83FF",
                        confirmButtonText: "Close"
                    }, function() {
                        window.location.href = baseUrl + "support";
                    });
                } else {
                    swal('', jObj.error, "error");
                }
            }
        });
    });
});

function init_load() {
    //hide label
    $("#accountSelectorMenuTitle").html('');
}