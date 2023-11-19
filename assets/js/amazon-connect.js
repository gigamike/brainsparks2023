var amazonConnectPopupWindow = null;
var urlAmazonConnectStream = baseUrl + "amazon_connect/window_open_amazon_connect_stream";

$(document).ready(function() {
    $('.sidebar-panel').on('click', function() {
        if ((amazonConnectPopupWindow == null) || (amazonConnectPopupWindow.closed)) {
            amazonConnectPopupWindow = amazon_connect_popup_window(urlAmazonConnectStream, "window_open_amazon_connect_stream", "320", "550");
            console.log("opening ccp popup the first time");
        }
        amazonConnectPopupWindow.focus();
    });

    $('.amazonConnectLogin').on('click', function() {
        if ((amazonConnectPopupWindow == null) || (amazonConnectPopupWindow.closed)) {
            amazonConnectPopupWindow = amazon_connect_popup_window(urlAmazonConnectStream, "window_open_amazon_connect_stream", "320", "550");
            console.log("opening ccp popup the first time");
        }
        amazonConnectPopupWindow.focus();
    });
});

function amazon_connect_popup_window(url, title, w, h) {
    var left = (screen.width / 2) - (w / 2);
    var top = (screen.height / 2) - (h / 2);
    return window.open(url, title, 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);
}