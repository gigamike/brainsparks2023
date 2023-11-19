var formValidator = null;

$(document).ready(function() {

    //prevent form submission by ENTER
    $(window).keydown(function(event) {
        if (event.keyCode === 13) {
            event.preventDefault();
            return false;
        }
    });

    var rules_options = {
        amount: { required: true }
    };

    formValidator = $("#budgetForm").validate({
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

        $("#emailTemplateForm")[0].reset();
    });

    $('#saveBtn').click(function() {
        $(this).blur();

        //validate form
        if (!$("#budgetForm").valid()) {
            return;
        }

        //save
        $.ajax({
            type: 'POST',
            url: baseUrl + 'settings/ajax-budget-threshold-save',
            data: $("#budgetForm").serialize(),
            beforeSend: function() {
                $("#spinnerModal").modal('show');
            },
            success: function(jObj) {
                $("#spinnerModal").modal("hide");
                if (jObj.successful) {
                    swal({
                        title: "",
                        text: "Budget threshold saved!",
                        type: "success",
                        confirmButtonColor: "#2C83FF",
                        confirmButtonText: "Close"
                    }, function() {

                    });
                } else {
                    swal('', jObj.error, "error");
                }
            }
        });
    });
});