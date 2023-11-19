
var formValidator = null;


$(document).ready(function () {
    //prevents caching
    $.ajaxSetup({cache: false});

    //tooltip
    $('[data-toggle="tooltip"]').tooltip();

    //i-check
    $('.i-checks').iCheck({
        checkboxClass: 'icheckbox_flat',
        radioClass: 'iradio_flat'
    });


    $('.i-checks-line').each(function () {
        $(this).iCheck({
            checkboxClass: 'icheckbox_line',
            radioClass: 'iradio_line',
            insert: '<div class="icheck_line-icon"></div>' + $(this).attr('attr-label'),
            uncheckedClass: 'ichecks-cb-unchecked'
        });
    });


    //knobs
    var timeout2;

    $("#mLvlManagerShare").knob({
        'release': function (v) {
            if (timeout2) {
                clearTimeout(timeout2);
            }
            timeout2 = setTimeout(function () {
                $('#mLvlManagerShare').val(v).trigger('change');
                $('#mLvlPartnerShare').val(100 - v).trigger('change');
                $("#managerAddPartnerMLCDiv .collapse-link-label").html('');
            }, 100);

        }
    });

    $("#mLvlPartnerShare").knob({
        'release': function (v) {
            if (timeout2) {
                clearTimeout(timeout2);
            }
            timeout2 = setTimeout(function () {
                $('#mLvlPartnerShare').val(v).trigger('change');
                $('#mLvlManagerShare').val(100 - v).trigger('change');
                $("#managerAddPartnerMLCDiv .collapse-link-label").html('');
            }, 100);

        }
    });

    $("#pLvlPartnerShare").knob({
        'release': function (v) {
            if (timeout2) {
                clearTimeout(timeout2);
            }
            timeout2 = setTimeout(function () {
                $('#pLvlPartnerShare').val(v).trigger('change');
                $('#pLvlAgentShare').val(100 - v).trigger('change');
                $("#managerAddPartnerPLCDiv .collapse-link-label").html('');
            }, 100);

        }
    });

    $("#pLvlAgentShare").knob({
        'release': function (v) {
            if (timeout2) {
                clearTimeout(timeout2);
            }
            timeout2 = setTimeout(function () {
                $('#pLvlAgentShare').val(v).trigger('change');
                $('#pLvlPartnerShare').val(100 - v).trigger('change');
                $("#managerAddPartnerPLCDiv .collapse-link-label").html('');
            }, 100);

        }
    });


});
