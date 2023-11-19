/*
 *
 * chart.js options
 *
 */

var chart_line_opts = {
    responsive: true,
    scales: {
        xAxes: [
            {
                display: true,
                gridLines: {display: false},
                labels: {show: true}
            }
        ],
        yAxes: [
            {
                display: true,
                gridLines: {display: true},
                labels: {show: true},
                ticks: {beginAtZero: true, min: 0}
            }
        ]
    },
    legend: {
        display: true,
        position: 'bottom',
        labels: {
            usePointStyle: true,
            pointStyle: 'circle'
        }
    }
};

var chart_bar_opts_nolabel = {
    responsive: true,
    scales: {
        xAxes: [
            {
                display: true,
                gridLines: {display: true},
                labels: {show: true},
                stacked: false,
                barThickness: 7,
                maxBarThickness: 7,
            }
        ],
        yAxes: [
            {
                display: true,
                gridLines: {display: false},
                labels: {show: true},
                stacked: false,
                ticks: {beginAtZero: true, min: 0},
                barThickness: 7,
                maxBarThickness: 7,
            }
        ]
    },
    legend: {
        display: false
    }
};

var chart_bar_opts_nolabel_smalldataset = {
    responsive: true,
    scales: {
        xAxes: [
            {
                display: true,
                gridLines: {display: true},
                labels: {show: true},
                stacked: false,
                barPercentage: 0.2,
                barThickness: 7,
                maxBarThickness: 7,
            }
        ],
        yAxes: [
            {
                display: true,
                gridLines: {display: false},
                labels: {show: true},
                stacked: false,
                ticks: {beginAtZero: true, min: 0},
                barThickness: 7,
                maxBarThickness: 7,
            }
        ]
    },
    legend: {
        display: false
    }
};

var chart_bar_opts = {
    responsive: true,
    scales: {
        xAxes: [
            {
                display: true,
                gridLines: {display: true},
                labels: {show: true},
                stacked: false,
                barThickness: 7,
                maxBarThickness: 7,
            }
        ],
        yAxes: [
            {
                display: true,
                gridLines: {display: false},
                labels: {show: true},
                stacked: false,
                ticks: {beginAtZero: true, min: 0},
                barThickness: 7,
                maxBarThickness: 7,
            }
        ]
    },
    legend: {
        display: true
    }
};

var chart_bar_stacked_opts = {
    responsive: true,
    scales: {
        xAxes: [
            {
                display: true,
                gridLines: {display: true},
                labels: {show: true},
                stacked: true
            }
        ],
        yAxes: [
            {
                display: true,
                gridLines: {display: true},
                labels: {show: true},
                stacked: true,
                ticks: {beginAtZero: true, min: 0}
            }
        ]
    },
    legend: {
        display: true
    }
};


var chart_bar_stacked_nogrid_opts = {
    responsive: true,
    scales: {
        xAxes: [
            {
                display: true,
                gridLines: {display: false},
                labels: {show: true},
                stacked: true
            }
        ],
        yAxes: [
            {
                display: true,
                gridLines: {display: false},
                labels: {show: true},
                stacked: true,
                ticks: {beginAtZero: true, min: 0}
            }
        ]
    },
    legend: {
        display: true
    }
};