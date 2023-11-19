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

    $.fn.select2.defaults.set("theme", "bootstrap");
    $(".select2-input").select2();

    var rules_options = {
        first_name: { required: true },
        last_name: { required: true },
        email: { required: true },
        mobile_phone: { required: true },
        position: { required: true }
    };

    formValidator = $("#userForm").validate({
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
        if (!$("#userForm").valid()) {
            return;
        }

        $.ajax({
            type: "POST",
            url: baseUrl + "users/ajax-save",
            data: $("#userForm").serialize(),
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
                        window.location.href = baseUrl + "users";
                    });
                } else {
                    swal('', jObj.error, "error");
                }
            }
        });
    });
});