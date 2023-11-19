$(document).ready(function() {
    $(window).keydown(function(event) {
        if (event.keyCode === 13) {
            event.preventDefault();
            return false;
        }
    });

    $("#sendBtn").click(function() {
        // Create a formdata object and add the files
        var data = new FormData(document.getElementById('chatForm'));
        $.each($(':file'), function(i, file) {
            data.append('file-' + i, file);
        });
        // append CSRF token in the form data
        data.append('csrfmhub', $('#csrfheaderid').val());

        $.ajax({
            url: baseUrl + 'consultations/ajax-chatbot-message-save',
            type: 'POST',
            async: false,
            data: data,
            cache: false,
            processData: false, // Don't process the files
            contentType: false, // Set content type to false as jQuery will tell the server its a query string request
            timeout: 30000,
            beforeSend: function() {
                $(".btn").prop('disabled', true);
            },
            fail: function() {
                return true;
            },
            done: function(jObj) {
                return true;
            },
            success: function(jObj) {
                $(".btn").prop('disabled', false);

                if (jObj.successful) {
                    $("#message").val('');

                    getMessages();

                    $("#messagesWrapper").append(chatBotIsTypingTemplate());
                    scrollToElement();

                    chatBotIsTyping()
                        .then(
                            function(result) {
                                $.ajax({
                                    url: baseUrl + "consultations/ajax-chatbot-response",
                                    type: 'POST',
                                    async: false,
                                    cache: false,
                                    timeout: 30000,
                                    beforeSend: function() {},
                                    fail: function() {
                                        return true;
                                    },
                                    done: function(jObj) {
                                        return true;
                                    },
                                    success: function(jObj) {
                                        $('#messagesWrapper .chatbotTypingWrapper').remove();

                                        if (jObj.successful) {
                                            getMessages();
                                            scroll();
                                        } else {
                                            swal('', jObj.error, "error");
                                        }
                                    }
                                });
                            }
                        );


                } else {
                    swal('', jObj.error, "error");
                }
            }
        });
    });

    $("#message").keyup(function(event) {
        if (event.keyCode === 13) {
            $("#sendBtn").click();
        }
    });

    init();
});

function init() {
    $.ajax({
        url: baseUrl + "consultations/ajax-chatbot-init",
        type: 'POST',
        async: false,
        cache: false,
        timeout: 30000,
        beforeSend: function() {
            $(".btn").prop('disabled', true);
        },
        fail: function() {
            return true;
        },
        done: function(jObj) {
            return true;
        },
        success: function(jObj) {
            $(".btn").prop('disabled', false);
            if (jObj.successful) {
                getMessages();
            } else {
                swal('', jObj.error, "error");
            }
        }
    });
}

function chatBotIsTyping() {
    return new Promise(function(resolve, reject) {
        setTimeout(function() {
            resolve("anything");
        }, 1000);
    });
}

function chatBotIsTypingTemplate() {
    var html = "";
    html += "<div class=\"chatbotTypingWrapper\">";
    html += "<div class=\"typingIndicator\">";
    html += "<span></span>";
    html += "<span></span>";
    html += "<span></span>";
    html += "</div>";
    html += "</div>";

    return html;
}


function scrollToElement() {
    // jQuery('#messagesWrapper').slimscroll({ scrollBy: '120px' });
}

function scroll() {
    var height = 0;
    $('#messagesWrapper li').each(function(i, value) {
        height += parseInt($(this).height());
    });
    height += '100';
    $('#messagesWrapper').animate({ scrollTop: height });
}

function getMessages() {
    $.ajax({
        url: baseUrl + 'consultations/ajax-chatbot-get-chat-messages',
        data: {
            'csrfmhub': $('#csrfheaderid').val()
        },
        type: 'POST',
        async: false,
        cache: false,
        timeout: 30000,
        beforeSend: function() {},
        fail: function() {
            return true;
        },
        done: function(jObj) {
            return true;
        },
        success: function(jObj) {
            $("#messagesWrapper").html(jObj.html);
            scroll();
        }
    });
}