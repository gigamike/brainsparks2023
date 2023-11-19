$(document).ready(function() {

    //prevent form submission by ENTER
    $(window).keydown(function(event) {
        if (event.keyCode === 13) {
            event.preventDefault();
            return false;
        }
    });

    $('#hubDaterangePicker .input-daterange').datepicker({
        keyboardNavigation: false,
        forceParse: false,
        autoclose: true,
        format: baseDateFormat,
        todayBtn: "linked"
    }).on('changeDate', function(e) {
        $('#hubDaterangePicker .actions-addon').css('display', 'inline');
    });

    $('#hubDaterangeApplyBtn').click(function() {
        $(this).blur();
        if ($('#hubDaterangePicker input[name=start]').val() !== "" && $('#hubDaterangePicker input[name=end]').val() !== "") {
            load_dashboard_metrics();
            $('#hubDaterangePicker .actions-addon').css('display', 'none');
        } else {
            date_range_alltime();
        }
    });

    $('#hubDaterangeCancelBtn').click(function() {
        $(this).blur();
        date_range_alltime();
    });

    $('.filterUserType').click(function() {
        $('.filterUserType').closest("li").removeClass("active");
        $(this).closest("li").addClass("active");

        $('#filterUserType').val($(this).attr("attr-user-type"));

        load_dashboard_metrics();
    });

    $('.filterApp').click(function() {
        $('.filterApp').closest("li").removeClass("active");
        $(this).closest("li").addClass("active");

        $('#filterApp').val($(this).attr("attr-app"));

        load_dashboard_metrics();
    });

    load_dashboard_metrics();
});

function date_range_alltime() {
    clear_date_range();
    $('#hubDaterangePicker input[name=start]').datepicker('update', $("#referenceDateStart").val());
    $('#hubDaterangePicker input[name=end]').datepicker('update', moment().format(baseDateFormat.toUpperCase()));

    load_dashboard_metrics();
}

function clear_date_range() {
    $('#hubDaterangePicker input').each(function() {
        $(this).datepicker('clearDates');
    });

    $('#hubDaterangePicker .actions-addon').css('display', 'none');
}

function date_range_alltime() {
    clear_date_range();
    $('#hubDaterangePicker input[name=start]').datepicker('update', $("#referenceDateStart").val());
    $('#hubDaterangePicker input[name=end]').datepicker('update', moment().format(baseDateFormat.toUpperCase()));

    load_dashboard_metrics();
}

function date_range_today() {
    clear_date_range();

    var today = moment().format(baseDateFormat.toUpperCase());
    $('#hubDaterangePicker input[name=start]').datepicker('update', today);
    $('#hubDaterangePicker input[name=end]').datepicker('update', today);

    load_dashboard_metrics();
}

function date_range_last7days() {
    clear_date_range();
    $('#hubDaterangePicker input[name=start]').datepicker('update', moment().subtract(6, 'days').format(baseDateFormat.toUpperCase()));
    $('#hubDaterangePicker input[name=end]').datepicker('update', moment().format(baseDateFormat.toUpperCase()));

    load_dashboard_metrics();
}

function date_range_last30days() {
    clear_date_range();
    $('#hubDaterangePicker input[name=start]').datepicker('update', moment().subtract(29, 'days').format(baseDateFormat.toUpperCase()));
    $('#hubDaterangePicker input[name=end]').datepicker('update', moment().format(baseDateFormat.toUpperCase()));

    load_dashboard_metrics();
}

function date_range_thismonth() {
    clear_date_range();
    $('#hubDaterangePicker input[name=start]').datepicker('update', moment().startOf('month').format(baseDateFormat.toUpperCase()));
    $('#hubDaterangePicker input[name=end]').datepicker('update', moment().endOf('month').format(baseDateFormat.toUpperCase()));

    load_dashboard_metrics();
}

function date_range_lastmonth() {
    clear_date_range();
    $('#hubDaterangePicker input[name=start]').datepicker('update', moment().subtract(1, 'month').startOf('month').format(baseDateFormat.toUpperCase()));
    $('#hubDaterangePicker input[name=end]').datepicker('update', moment().subtract(1, 'month').endOf('month').format(baseDateFormat.toUpperCase()));

    load_dashboard_metrics();
}

function date_range_thisyear() {
    clear_date_range();
    $('#hubDaterangePicker input[name=start]').datepicker('update', moment().startOf('year').format(baseDateFormat.toUpperCase()));
    $('#hubDaterangePicker input[name=end]').datepicker('update', moment().endOf('year').format(baseDateFormat.toUpperCase()));

    load_dashboard_metrics();
}

function date_range_lastyear() {
    clear_date_range();
    $('#hubDaterangePicker input[name=start]').datepicker('update', moment().subtract(1, 'year').startOf('year').format(baseDateFormat.toUpperCase()));
    $('#hubDaterangePicker input[name=end]').datepicker('update', moment().subtract(1, 'year').endOf('year').format(baseDateFormat.toUpperCase()));

    load_dashboard_metrics();
}

function load_dashboard_metrics() {
    metrics();
    pieChart();
    barChart();
}

function metrics() {
    $.ajax({
        type: 'POST',
        url: baseUrl + 'dashboard/ajax-metrics',
        data: {
            'filterStart': $('#hubDaterangePicker input[name=start]').val(),
            'filterEnd': $('#hubDaterangePicker input[name=end]').val(),
            'filterUserType': $('#filterUserType').val(),
            'filterApp': $('#filterApp').val()
        },
        success: function(jObj) {
            if (jObj.successful) {
                $('.countOpenTickets').html(jObj.countOpenTickets);
                $('.countResolvedTickets').html(jObj.countResolvedTickets);
                $('.countReOpenTickets').html(jObj.countReOpenTickets);
                $('.countClosedTickets').html(jObj.countClosedTickets);
            } else {
                $('.countOpenTickets').html(0);
                $('.countResolvedTickets').html(0);
                $('.countReOpenTickets').html(0);
                $('.countClosedTickets').html(0);
            }

        }
    });
}

function pieChart() {
    $.ajax({
        type: 'POST',
        url: baseUrl + 'dashboard/ajax-pie-chart',
        data: {
            'filterStart': $('#hubDaterangePicker input[name=start]').val(),
            'filterEnd': $('#hubDaterangePicker input[name=end]').val(),
            'filterUserType': $('#filterUserType').val(),
            'filterApp': $('#filterApp').val()
        },
        success: function(jObj) {
            if (jObj.successful) {
                var data = {
                    labels: [
                        'Open',
                        'Resolved',
                        'Re-open',
                        'Closed'
                    ],
                    datasets: [{
                        label: 'Ticket Status',
                        data: jObj.data,
                        backgroundColor: [
                            'rgb(54, 162, 235)',
                            'rgb(0, 128, 0)',
                            'rgb(255, 205, 86)',
                            'rgb(128,128,128)'
                        ],
                        hoverOffset: 4
                    }]
                };

                var config = {
                    type: 'pie',
                    data: data,
                    options: {
                        responsive: true
                    }
                };

                $("#pieChartWrapper").html('<canvas id="pieChart"></canvas>');
                var myChart = new Chart(
                    document.getElementById('pieChart'),
                    config
                );
            }

        }
    });
}

function barChart() {
    $.ajax({
        type: 'POST',
        url: baseUrl + 'dashboard/ajax-bar-chart',
        data: {
            'filterStart': $('#hubDaterangePicker input[name=start]').val(),
            'filterEnd': $('#hubDaterangePicker input[name=end]').val(),
            'filterUserType': $('#filterUserType').val(),
            'filterApp': $('#filterApp').val()
        },
        success: function(jObj) {
            if (jObj.successful) {
                var labels = jObj.labels;

                var data = {
                    labels: labels,
                    datasets: [{
                            label: 'Open',
                            data: jObj.dataOpenTickets,
                            borderColor: 'rgb(54, 162, 235)',
                            backgroundColor: 'rgb(54, 162, 235)'
                        },
                        {
                            label: 'Resolved',
                            data: jObj.dataResolvedTickets,
                            borderColor: 'rgb(0, 128, 0)',
                            backgroundColor: 'rgb(0, 128, 0)'
                        },
                        {
                            label: 'Re-open',
                            data: jObj.dataReopenTickets,
                            borderColor: 'rgb(255, 205, 86)',
                            backgroundColor: 'rgb(255, 205, 86)'
                        },
                        {
                            label: 'Closed',
                            data: jObj.dataClosedTickets,
                            borderColor: 'rgb(128,128,128)',
                            backgroundColor: 'rgb(128,128,128)'
                        }
                    ]
                };

                var config = {
                    type: 'bar',
                    data: data,
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            title: {
                                display: true,
                                text: 'Tickets Summary'
                            }
                        }
                    }
                };

                $("#barChartWrapper").html('<canvas id="barChart"></canvas>');
                var myChart = new Chart(
                    document.getElementById('barChart'),
                    config
                );
            }

        }
    });
}