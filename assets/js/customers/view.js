var dtObj;

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

    new Clipboard('.copy-to-clipboard', {
        text: function(trigger) {
            return $($(trigger).attr('data-clipboard-target')).val();
        }
    });

    dtObj = $('#dtInvoicesTbl').dataTable({
        dom: '<<t><"row m-t-sm"<"col-sm-6"i><"col-sm-6"p>>>',
        processing: true,
        serverSide: true,
        responsive: false,
        autoWidth: false,
        scrollX: true,
        scrollY: true,
        ajax: {
            "url": baseUrl + "customers/ajax-dt-get-invoices",
            "data": function(d) {
                d.customer_id = $('#customer_id').val();
                d.searchText = $('#dtSearchText').val();
            }
        },
        columns: [
            { "data": "invoice_u_code" },
            { "data": "date_purchased", "searchable": false },
            { "data": "product_u_code" },
            { "data": "product_name" },
            { "data": "price" },
            { "data": "quantity" },
            { "data": "date_added", "searchable": false }
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
    $('#dtInvoicesTbl').on('draw.dt', function(e, settings) {
        //redefine tooltips
        $('#dtInvoicesTbl [data-toggle="tooltip"]').tooltip('destroy');
        $('#dtInvoicesTbl [data-toggle="tooltip"]').tooltip();
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

    $('#recommenderBtn').click(function() {
        $('#recommenderModal').modal('show');
    });

    $('#recipe').change(function() {
        getProducts();
    });

    getProducts();
});

function dt_hide_search() {
    $('#dtSearchContainer').hide();
    $('#toggleSearchBtn .action-label').html('Search');
}

function dt_show_search() {
    $('#dtSearchContainer').show();
    $('#toggleSearchBtn .action-label').html('Close');
}

function getProducts() {
    $.ajax({
        type: "POST",
        url: baseUrl + "customers/ajax-get-products",
        data: {
            'recipe': $('#recipe').val()
        },
        success: function(jObj) {
            if (jObj.successful) {
                $('.productsWrapper').html(jObj.html);
            } else {
                swal('Error', result.error, "error");
            }
        }
    });
}