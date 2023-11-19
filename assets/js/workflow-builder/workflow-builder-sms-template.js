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

    $('.input-group.date.single').datepicker({
        format: baseDateFormat,
        autoclose: true,
        keyboardNavigation: false
    });

    $('.input-group.date.date-from').datepicker({
        format: baseDateFormat,
        autoclose: true,
        keyboardNavigation: false
    }).on('changeDate', function(e) {
        if ($('#' + $(':input', this).attr('id')).val() == "") {
            return;
        }

        var from_date = new Date(e.date);
        var dateParts = $('#' + $(':input', this).attr('attr-target-date')).val().split("/");
        var to_date = new Date();

        /**
         * CODE BRANCHING HERE - DATE FORMAT
         */
        if (baseDateFormat === "mm/dd/yyyy") {
            to_date.setFullYear(dateParts[2], parseInt(dateParts[0]) - 1, dateParts[1]);
        } else {
            //default format is dd/mm/yyyy
            to_date.setFullYear(dateParts[2], parseInt(dateParts[1]) - 1, dateParts[0]);
        }

        if (from_date > to_date) {
            $(this).datepicker('update', to_date);
        }
    });

    $('.input-group.date.date-to').datepicker({
        format: baseDateFormat,
        autoclose: true,
        keyboardNavigation: false
    }).on('changeDate', function(e) {
        if ($('#' + $(':input', this).attr('id')).val() == "") {
            return;
        }

        var to_date = new Date(e.date);
        var dateParts = $('#' + $(':input', this).attr('attr-target-date')).val().split("/");
        var from_date = new Date();

        /**
         * CODE BRANCHING HERE - DATE FORMAT
         */
        if (baseDateFormat === "mm/dd/yyyy") {
            from_date.setFullYear(dateParts[2], parseInt(dateParts[0]) - 1, dateParts[1]);
        } else {
            //default format is dd/mm/yyyy
            from_date.setFullYear(dateParts[2], parseInt(dateParts[1]) - 1, dateParts[0]);
        }

        if (to_date < from_date) {
            $(this).datepicker('update', from_date);
        }

    });

    $(".date-filter-operator").on('change', function() {
        var target_div = $(this).attr('attr-target-div');

        //QUERY_FILTER_IS_BETWEEN = 13
        if (parseInt($(this).val()) === 13) {
            $("#" + target_div + " .date-filter-single-input-div").css('display', 'none');
            $("#" + target_div + " .date-filter-multi-input-div").css('display', 'block');

            $("#" + target_div + " .input-group.date > :input").val("");

            $("#" + target_div + ' .input-group.date.date-single').datepicker('update', new Date()).datepicker('update', '');
            $("#" + target_div + ' .input-group.date.date-from').datepicker('update', new Date()).datepicker('update', '');
            $("#" + target_div + ' .input-group.date.date-to').datepicker('update', new Date()).datepicker('update', '');
        } else if ($("#" + target_div + " .date-filter-single-input-div").css('display') == "none") {
            $("#" + target_div + " .date-filter-single-input-div").css('display', 'block');
            $("#" + target_div + " .date-filter-multi-input-div").css('display', 'none');

            $("#" + target_div + " .input-group.date > :input").val("");
            $("#" + target_div + ' .input-group.date.date-single').datepicker('update', new Date()).datepicker('update', '');
            $("#" + target_div + ' .input-group.date.date-from').datepicker('update', new Date()).datepicker('update', '');
            $("#" + target_div + ' .input-group.date.date-to').datepicker('update', new Date()).datepicker('update', '');
        }
    });


    dtObj = $('#dtWorkflowBuilderSMSTemplateSummaryTbl').dataTable({
        dom: '<<t><"row m-t-sm"<"col-sm-6"i><"col-sm-6"p>>>',
        processing: true,
        serverSide: true,
        responsive: false,
        autoWidth: false,
        scrollX: true,
        scrollY: true,
        pageLength: 25,
        ajax: {
            "url": baseUrl + "workflow-builder/ajax-manager-dt-get-work-builder-sms-template",
            "data": function(d) {
                d.searchText = $('#dtSearchText').val();
                d.filterIsSystemDefault = $('input[name=filterIsSystemDefault]:checked').val();
                d.filterNameOperator = $('#filterNameOperator').val() !== null ? $('#filterNameOperator').val() : "";
                d.filterName = $('#filterName').val();
                d.filterDateAddedOperator = $('#filterDateAddedOperator').val() !== null ? $('#filterDateAddedOperator').val() : "";
                d.filterDateAdded = $('#filterDateAdded').val();
                d.filterDateAddedFrom = $('#filterDateAddedFrom').val();
                d.filterDateAddedTo = $('#filterDateAddedTo').val();

                d.columnHeader = $("#partnerWorkflowBuilderSMSTemplateColumnsForm").serialize();
            }
        },
        columns: [
            { "data": "name" },
            { "data": "template" },
            { "data": "date_added", "searchable": false },
            { "data": "actions", "orderable": false, "searchable": false, "className": "td-wrapping-line" }
        ],
        order: [
            [$('#order_col').val(), $('#order_dir').val()]
        ],
        language: {
            "search": "_INPUT_", //search
            "searchPlaceholder": "Search Records",
            "lengthMenu": "Show _MENU_", //label
            "emptyTable": "None Found.",
            "info": "Showing _START_ - _END_ of _TOTAL_", //label
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
    $('#dtWorkflowBuilderSMSTemplateSummaryTbl').on('draw.dt', function(e, settings) {
        $('#filtersCountDiv').html((parseInt(settings.json.filtersCount) <= 0 ? "" : " " + settings.json.filtersCount));
        $('#dtWorkflowBuilderSMSTemplateSummaryTbl [data-toggle="tooltip"]').tooltip();
    });


    //search
    $('#dtSearchText').bind('keyup', function(e) {
        if (e.keyCode === 13) {
            dtObj.api().ajax.reload();
        }
    });

    $('#dtSearchBtn').click(function() {
        $(this).blur();
        dtObj.api().ajax.reload();
    });

    //filter
    $("input[name=filterIsSystemDefault]").on("change", function() {
        $('#filterIsSystemDefaultDiv .btn').removeClass('btn-primary');
        $(this).parent().addClass('btn-primary');
        dtObj.api().ajax.reload();
    });


    //reset button
    $('#filterResetBtn').click(function() {
        $(this).blur();
        $("#dtSearchText").val('');
        $('#filterWorkflowBuilderSMSTemplateDiv .filter-set').val('');
        $("#filterWorkflowBuilderSMSTemplateDiv .select2-input").val("").trigger("change");
        $("#filterWorkflowBuilderSMSTemplateDiv .select2-input.filter-operator").each(function() {
            $(this).val([$("option:first", this).val()]).trigger("change");
        });

        //filter
        $('#filterIsSystemDefaultDiv .btn').removeClass('btn-primary').removeClass('active');
        $('input:radio[name=filterIsSystemDefault]').each(function() {
            $(this).prop('checked', false);
        });

        dtObj.api().ajax.reload();
    });


    //apply button
    $('#filterApplyBtn').click(function() {
        $(this).blur();
        $("#dtSearchText").val('');
        dtObj.api().ajax.reload();
    });

    $('.filter-value').bind('keyup', function(e) {
        if (e.keyCode === 13) {
            $('#filterApplyBtn').focus().click();
        }
    });

    //toggle visible columns
    $('.dt-toggle-col-visibility').on('click', function(e) {
        //e.preventDefault();
        var column = dtObj.api().column($(this).val());
        column.visible(!column.visible());

        //update sess
        $.ajax({
            type: "POST",
            url: baseUrl + "workflow-builder/ajax-set-workflow-builder-sms-template-visible-cols",
            data: $("#partnerWorkflowBuilderSMSTemplateColumnsForm").serialize()
        });
    });

    $('#toggleSearchBtn').click(function() {

        var state = $('#toggleSearchBtn .action-label').html();

        if (state == 'Search')
            dt_show_search();
        else
            dt_hide_search();

        dt_hide_columns();
        dt_hide_filters();

    });

    $('#toggleColumnWorkflowBuilderSMSTemplateBtn').click(function() {

        var state = $('#toggleColumnWorkflowBuilderSMSTemplateBtn .action-label').html();

        if (state == 'Columns')
            dt_show_columns();
        else
            dt_hide_columns();

        dt_hide_search();
        dt_hide_filters();

    });

    $('#toggleFilterWorkflowBuilderSMSTemplateBtn').click(function() {
        var state = $('#toggleFilterWorkflowBuilderSMSTemplateBtn .action-label').html();

        if (state == 'Filter')
            dt_show_filters();
        else
            dt_hide_filters();

        dt_hide_columns();
        dt_hide_search();

    });

});


function init() {
    setTimeout(function() {
        if (dtObj !== null) {
            $('input[type=checkbox][name=columnHeader\\[\\]]').each(function() {
                if (!this.checked) {
                    dtObj.api().column($(this).val()).visible(false);
                }
            });
        }
    }, 310);
}

function send_to(id) {
    window.location.href = baseUrl + "workflow-builder/sms-template-update/" + $('#' + id).attr('attr-workflow-builder-sms-template');
}

function view(id) {
    window.location.href = baseUrl + "workflow-builder/sms-template-view/" + $('#' + id).attr('attr-workflow-builder-sms-template');
}

function delete_to(id) {
    swal({
        title: "",
        text: "Are you sure you want to delete this SMS Template?",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#2C83FF",
        confirmButtonText: "Yes",
        closeOnConfirm: true
    }, function() {
        $.ajax({
            type: 'POST',
            url: baseUrl + 'workflow-builder/ajax-sms-template-delete',
            data: {
                'sms_template_id': $('#' + id).attr('attr-workflow-builder-sms-template')
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
                        window.location.href = baseUrl + "workflow-builder/sms-template";
                    });
                    window.location.href = baseUrl + "workflow-builder/sms-template";
                } else {
                    $("#spinnerModal").modal("hide");
                    swal('', jObj.error, "error");
                }
            }
        });
    });
}

function dt_hide_search() {

    $('#dtSearchContainer').hide();
    $('#toggleSearchBtn .action-label').html('Search');
}

function dt_hide_columns() {

    $('#dtColumnContainer').hide();
    $('#toggleColumnWorkflowBuilderSMSTemplateBtn .action-label').html('Columns');
}

function dt_hide_filters() {

    $('#dtFilterContainer').hide();
    $('#toggleFilterWorkflowBuilderSMSTemplateBtn .action-label').html('Filter');
}

function dt_show_search() {

    $('#dtSearchContainer').show();
    $('#toggleSearchBtn .action-label').html('Close');
}

function dt_show_columns() {

    $('#dtColumnContainer').show();
    $('#toggleColumnWorkflowBuilderSMSTemplateBtn .action-label').html('Close');
}

function dt_show_filters() {

    $('#dtFilterContainer').show();
    $('#toggleFilterWorkflowBuilderSMSTemplateBtn .action-label').html('Close');
}