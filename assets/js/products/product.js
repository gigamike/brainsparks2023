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
        name: { required: true },
        price: { required: true }
    };

    formValidator = $("#productFrm").validate({
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
        if (!$("#productFrm").valid()) {
            return;
        }

        // Create a formdata object
        var dataset = new FormData(document.getElementById('productFrm'));

        //add the files if available
        if ($("#photo").val() !== "") {
            $.each($(':file'), function(i, file) {
                dataset.append('file-' + i, file);
            });
        }

        $.ajax({
            type: "POST",
            url: baseUrl + "products/ajax-save",
            data: dataset,
            cache: false,
            processData: false, // Don't process the files
            contentType: false, // Set content type to false as jQuery will tell the server its a query string request
            success: function(jObj) {
                if (jObj.successful) {
                    swal({
                        title: "Success!",
                        text: "Product was saved succesfully!",
                        type: "success",
                        allowEscapeKey: false,
                        closeOnConfirm: true,
                    }, function() {
                        window.location.href = baseUrl + "products";
                    });
                } else {
                    swal('Error in Saving Products', result.error, "error");
                }
            }
        });
    });
});