var dtObj;

$(document).ready(function() {

    //prevent form submission by ENTER
    $(window).keydown(function(event) {
        if (event.keyCode === 13) {
            event.preventDefault();
            return false;
        }
    });

    dtObj = $('#dtProductsTbl').dataTable({
        dom: '<<t><"row m-t-sm"<"col-sm-6"i><"col-sm-6"p>>>',
        processing: true,
        serverSide: true,
        responsive: false,
        autoWidth: false,
        scrollX: true,
        scrollY: true,
        ajax: {
            "url": baseUrl + "products/ajax-dt-get-products",
            "data": function(d) {
                d.searchText = $('#dtSearchText').val();
            }
        },
        columns: [
            { "data": "photo", "orderable": false, "searchable": false },
            { "data": "u_code" },
            { "data": "name" },
            { "data": "price" },
            { "data": "date_added", "searchable": false },
            { "data": "actions", "orderable": false, "searchable": false }
        ],
        order: [
            [2, 'asc']
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
    $('#dtProductsTbl').on('draw.dt', function(e, settings) {
        //redefine tooltips
        $('#dtProductsTbl [data-toggle="tooltip"]').tooltip('destroy');
        $('#dtProductsTbl [data-toggle="tooltip"]').tooltip();
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