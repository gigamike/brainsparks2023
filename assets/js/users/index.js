var dtObj;

$(document).ready(function() {

    //prevent form submission by ENTER
    $(window).keydown(function(event) {
        if (event.keyCode === 13) {
            event.preventDefault();
            return false;
        }
    });

    dtObj = $('#dtUsersTbl').dataTable({
        dom: '<<t><"row m-t-sm"<"col-sm-6"i><"col-sm-6"p>>>',
        processing: true,
        serverSide: true,
        responsive: false,
        autoWidth: false,
        scrollX: true,
        scrollY: true,
        ajax: {
            "url": baseUrl + "users/ajax-dt-get-users",
            "data": function(d) {
                d.searchText = $('#dtSearchText').val();
            }
        },
        columns: [
            { "data": "u_code" },
            { "data": "first_name" },
            { "data": "last_name" },
            { "data": "email" },
            { "data": "mobile_phone" },
            { "data": "position" },
            { "data": "date_added", "searchable": false },
            { "data": "actions", "orderable": false, "searchable": false }
        ],
        order: [
            [0, 'asc']
        ],
        language: {
            "search": "_INPUT_", //search
            "searchPlaceholder": "Search Records",
            "lengthMenu": "Show _MENU_", //label
            "emptyTable": "None Found.",
            "info": "_START_ to _END_ of _TOTAL_", //label
            "paginate": { "next": "<i class=\"fa fa-angle-right\"></i>", "previous": "<i class=\"fa fa-angle-left\"></i>" } //pagination
        }

    });

    $(window).resize(function() {
        dtObj.fnAdjustColumnSizing();
    });

    $(".navbar-minimalize").click(function() {
        // add delay since inspinia.js adds delay in SmoothlyMenu()
        setTimeout(function() {
            dtObj.fnAdjustColumnSizing();
        }, 310);
    });

    //event after dt reload
    $('#dtUsersTbl').on('draw.dt', function(e, settings) {
        //redefine tooltips
        $('#dtUsersTbl [data-toggle="tooltip"]').tooltip('destroy');
        $('#dtUsersTbl [data-toggle="tooltip"]').tooltip();
    });

    $('#dtSearchText').bind('keyup', function(e) {
        if (e.keyCode === 13) {
            dtObj.api().ajax.reload();
        }
    });

    $('#dtSearchBtn').click(function() {
        dtObj.api().ajax.reload();
    });

    $('#toggleSearchBtn').click(function() {

        var state = $('#toggleSearchBtn .action-label').html();

        if (state == 'Search')
            dt_show_search();
        else
            dt_hide_search();
    });

});

function dt_hide_search() {
    $('#dtSearchContainer').hide();
    $('#toggleSearchBtn .action-label').html('Search');
}

function dt_show_search() {
    $('#dtSearchContainer').show();
    $('#toggleSearchBtn .action-label').html('Close');
}

function delete_to(id) {
    swal({
        title: "Are you sure you want to delete this User?",
        text: "This action cannot be undone. All records related to this User will be deleted too.",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#0072bc",
        confirmButtonText: "Yes!",
        closeOnConfirm: true
    }, function() {
        $.ajax({
            type: 'POST',
            url: baseUrl + 'users/ajax-delete',
            data: {
                'user_id': id
            },
            beforeSend: function() {
                $("#spinnerModal").modal('show');
            },
            success: function(jObj) {
                if (jObj.successful) {
                    swal({
                        title: "",
                        text: jObj.message,
                        type: "success",
                        confirmButtonColor: "#2C83FF",
                        confirmButtonText: "Close"
                    }, function() {

                    });

                    window.location.href = baseUrl + "users";
                } else {
                    $("#spinnerModal").modal("hide");
                    swal('Ooops!', jObj.error, "error");
                }
            }
        });
    });
}