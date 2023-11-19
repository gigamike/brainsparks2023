var formValidator = null;

$(document).ready(function() {
    //prevents caching
    $.ajaxSetup({ cache: false });

    var rules_options = {};

    formValidator = $("#bulkImportForm").validate({
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
        if (!$("#bulkImportForm").valid()) {
            return;
        }

        // Create a formdata object
        var dataset = new FormData(document.getElementById('bulkImportForm'));

        $.ajax({
            type: 'POST',
            url: baseUrl + 'customers/ajax-bulk-import-save',
            data: dataset,
            cache: false,
            processData: false, // Don't process the files
            contentType: false, // Set content type to false as jQuery will tell the server its a query string request
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

                    });

                    window.location.href = baseUrl + "customers";
                } else {
                    $("#spinnerModal").modal("hide");
                    swal('Ooops!', jObj.error, "error");
                }
            }
        });
    });
});