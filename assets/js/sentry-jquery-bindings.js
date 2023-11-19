$(function(){
    // if any ajax error occured, report it to sentry
    $(document).ajaxError(function(event, jqxhr, settings, thrownError){
        Sentry.captureException(new Error('AJAX Call Failed. url: ' + settings.url + ', status:' + jqxhr.status));
     });
});

