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
        first_name: { required: true },
        last_name: { required: true },
        email: { required: true },
        mobile_phone: { required: true }
    };

    formValidator = $("#customerForm").validate({
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

    $('#saveBtn').click(function() {
        $(this).blur();

        //validate form
        if (!$("#customerForm").valid()) {
            return;
        }

        $.ajax({
            type: "POST",
            url: baseUrl + "customers/ajax-save",
            data: $("#customerForm").serialize(),
            beforeSend: function() {
                $("#spinnerModal").modal('show');
            },
            success: function(jObj) {
                $("#spinnerModal").modal("hide");
                if (jObj.successful) {
                    swal({
                        title: "",
                        text: 'Plate Number saved!',
                        type: "success",
                        confirmButtonColor: "#2C83FF",
                        confirmButtonText: "Close"
                    }, function() {
                        window.location.href = baseUrl + "customers";
                    });
                } else {
                    swal('', jObj.error, "error");
                }
            }
        });
    });
});