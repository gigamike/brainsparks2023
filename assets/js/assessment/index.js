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

    $('.i-checks').iCheck({
        checkboxClass: 'icheckbox_square-green',
        radioClass: 'iradio_square-green',
    });


    $("#workflowBuilderForm").steps({
        bodyTag: "fieldset",
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

            // Start validation; Prevent going forward if false
            return form.valid();
        },
        onStepChanged: function(event, currentIndex, priorIndex) {},
        onFinishing: function(event, currentIndex) {
            var form = $(this);

            // Disable validation on fields that are disabled.
            // At this point it's recommended to do an overall check (mean ignoring only disabled fields)
            form.validate().settings.ignore = ":disabled";

            // Start validation; Prevent form submission if false
            return form.valid();
        },
        onFinished: function(event, currentIndex) {
            var form = $(this);

            // Submit form input
            // form.submit();

            save();
        }
    }).validate({
        errorPlacement: function(error, element) {
            element.before(error);
        },
        rules: {
            name: {
                required: true,
                maxlength: 255
            },
            description: { required: true },
            execute: { required: true },
            execute_run: { required: true },
            execute_time: { required: true }
        },
        ignore: [":disabled", ":hidden"],
        highlight: function(element) {
            $(element).closest('.form-group').addClass('has-error');
        },
        unhighlight: function(element) {
            $(element).closest('.form-group').removeClass('has-error');
        },
        errorPlacement: function(error, element) {
            if (element.hasClass('select2-input') && element.next('.select2-container').length) {
                error.insertAfter(element.next('.select2-container'));
            } else if (element.parent('.input-group').length) {
                error.insertAfter(element.parent());
            } else if (element.is(':checkbox')) {
                error.insertAfter(element.parent());
            } else if (element.is(':radio')) {
                error.insertAfter(element.parent().siblings().last());
            } else {
                error.insertAfter(element);
            }
        }
    });

    $('.actions ul > li:nth-child(1) a').addClass('btn btn-md btn-default btn-square btn-w-sm m-t-xs m-l-xs');
    $('.actions ul > li:nth-child(2) a').addClass('btn btn-md btn-primary btn-square btn-w-sm m-t-xs m-l-xs');
    $('.actions ul > li:last-child a').addClass('btn btn-md btn-primary btn-square btn-w-sm m-t-xs m-l-xs');

});

function save() {
    $.ajax({
        type: "POST",
        url: baseUrl + "assessment/ajax-save",
        data: $("#customerForm").serialize(),
        beforeSend: function() {
            $("#spinnerModal").modal('show');
        },
        success: function(jObj) {
            $("#spinnerModal").modal("hide");
            if (jObj.successful) {
                swal({
                    title: "",
                    text: 'Assessment submitted!',
                    type: "success",
                    confirmButtonColor: "#2C83FF",
                    confirmButtonText: "Close"
                }, function() {
                    window.location.href = baseUrl + "consultations";
                });
            } else {
                swal('', jObj.error, "error");
            }
        }
    });
}