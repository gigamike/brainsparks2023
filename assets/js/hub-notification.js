$(document).ready(function() {
    // every 1 minute as cron runs at least 1 minute interval
    setInterval(function() {
        notification();
    }, 60000);
    notification();
});

function notification() {
    $.ajax({
        url: baseUrl + "notification/ajax-notification",
        success: function(jObj) {
            if (jObj.successful) {
                $("#notificationTotal").html(jObj.notificationTotal);

                $("#notificationEmailTotal").html(jObj.notificationEmailTotal);
                $("#notificationEmailDate").html(jObj.notificationEmailDate);

                $("#notificationSmsTotal").html(jObj.notificationSmsTotal);
                $("#notificationSmsDate").html(jObj.notificationSmsDate);

                $("#notificationChatTotal").html(jObj.notificationChatTotal);
                $("#notificationChatDate").html(jObj.notificationChatDate);
            } else {
                $("#notificationTotal").html("");

                $("#notificationEmailTotal").html("");
                $("#notificationEmailDate").html("");

                $("#notificationSmsTotal").html("");
                $("#notificationSmsDate").html("");

                $("#notificationChatTotal").html("");
                $("#notificationChatDate").html("");
            }
        }
    });
}