var isInit = false;

var currentContactId = null;

$(document).ready(function() {
    $('[data-toggle="tooltip"]').tooltip();

    amazon_connect_init();
    $('iframe').attr("frameBorder", "0");
});

var containerDiv = document.getElementById("container-div-amazon-connect");
var instanceURL = amazonConnectCCPURL + '/ccp-v2/softphone';

window.myCPP = window.myCPP || {};

// initialize the streams api
function amazon_connect_init() {
    // initialize the ccp
    connect.core.initCCP(containerDiv, {
        ccpUrl: instanceURL, // REQUIRED
        loginPopup: true, // optional, defaults to `true`
        loginPopupAutoClose: true, // optional, defaults to `false`
        loginOptions: { // optional, if provided opens login in new window
            autoClose: true, // optional, defaults to `false`
            height: 600, // optional, defaults to 578
            width: 400, // optional, defaults to 433
            top: 0, // optional, defaults to 0
            left: 0 // optional, defaults to 0
        },
        region: awsRegion, // REQUIRED for `CHAT`, optional otherwise
        softphone: { // optional, defaults below apply if not provided
            allowFramedSoftphone: true, // optional, defaults to false
            disableRingtone: false, // optional, defaults to false
            ringtoneUrl: baseUrl + "assets/ringtones/home-ringtone-4438.mp3" // optional, defaults to CCP’s default ringtone if a falsy value is set
        },
        pageOptions: { //optional
            enableAudioDeviceSettings: true, //optional, defaults to 'false'
            enablePhoneTypeSettings: true //optional, defaults to 'true'
        },
        ccpAckTimeout: 5000, //optional, defaults to 3000 (ms)
        ccpSynTimeout: 3000, //optional, defaults to 1000 (ms)
        ccpLoadTimeout: 50000 //optional, defaults to 5000 (ms)
    });

    /*
     *
     * This works when user is loggedin
     *
     */
    connect.agent(subscribeToAgentEvents);
    connect.contact(subscribeContactEvents);

    function subscribeToAgentEvents(agent) {
        // var state = agent.getState();
        // console.log(state);

        agent.onRefresh(function(agent) {
            console.log('Agent Events onRefresh');
            console.log(agent);
            document.getElementById("amazonConnectLoginMessage").innerHTML = '';
            document.getElementById("container-div-amazon-connect").style.display = "block";
        });

        /*
         * Set user change status i.e. from Break To Available
         */
        agent.onStateChange(function(agentStateChange) {
            console.log('Agent Events onStateChange');
            console.log(agentStateChange);
            stateChange(agentStateChange.oldState, agentStateChange.newState);
        });

        /*
         * Set status to Offline
         */
        agent.onOffline(function(agent) {
            console.log('Agent Events onOffline');
            console.log(agent);
            stateChange(agentStateChange.oldState, agentStateChange.newState);
        });
    }

    /*
     * When user logout
     */
    const eventBus = connect.core.getEventBus();
    eventBus.subscribe(connect.EventType.TERMINATED, () => {
        logout();
    });

    function subscribeContactEvents(contact) {
        var attributeMap = contact.getAttributes();
        console.log("Contact getAttributes");
        console.log(attributeMap);

        contact.onRefresh(function(contact) {
            // always triggered as CCP automatically refresh
            console.log("Contact Events onRefresh");
            console.log(contact);
        });
        contact.onIncoming(function(contact) {
            console.log("Contact Events onIncoming");
            console.log(contact);

            currentContactId = contact.contactId;
        });
        contact.onPending(function(contact) {
            console.log("Contact Events onPending");
        });
        contact.onConnecting(function(contact) {
            // Call is ringing
            console.log("Contact Events onConnecting");
            console.log(contact);

            displayStatus(contact.contactId);

            currentContactId = contact.contactId;
        });
        contact.onAccepted(function(contact) {
            // Call Accepted or answered
            console.log("Contact Events onAccepted");
            console.log(contact);

            // redirectToApplication(contact.contactId);
            // Problem with miss call and opening the lead, better when they talk or connected
            // openNewWindow(contact.contactId);

            currentContactId = contact.contactId;

            // $('#stopRecordingBtn').prop("disabled", false);
            $('#pauseRecordingBtn').prop("disabled", false);
            $('#resumeRecordingBtn').prop("disabled", true);

            $("#recordStatus").addClass('btn-record-recording');
        });
        contact.onMissed(function(contact) {
            // Call was missed, didnt answer by agent and caller drop the call
            console.log("Contact Events onMissed");
            console.log(contact);

            // contactOnMissed(contact.contactId);

            currentContactId = null;
        });
        contact.onEnded(function(contact) {
            // Call ended
            console.log("Contact Events onEnded");
            console.log(contact);

            currentContactId = null;
        });
        contact.onDestroy(function(contact) {
            console.log("Contact Events onDestroy");
            console.log(contact);

            currentContactId = null;

            // $('#stopRecordingBtn').prop("disabled", true);
            $('#pauseRecordingBtn').prop("disabled", true);
            $('#resumeRecordingBtn').prop("disabled", true);

            $("#recordStatus").removeClass('btn-record-recording');
        });
        contact.onACW(function(contact) {
            // Call After Call Work
            console.log("Contact Events onACW");
            console.log(contact);

            currentContactId = null;
        });
        contact.onConnected(function(contact) {
            // Call Connected
            console.log("Contact Events onConnected");
            console.log(contact);

            currentContactId = contact.contactId;
        });
        contact.onError(function(contact) {
            console.log("Contact Events onError");
            console.log(contact);

            currentContactId = null;
        });
    }
}

function stateChange(oldState, newState) {
    // Everytime the CCP is closed, it always calls the state change and trigger init.
    // We need to catch every init so CRM user wont refresh, we only refresh on first init as first action is to login
    if (!isInit) {
        $.ajax({
            type: "POST",
            url: baseUrl + "amazon-connect/ajax-agent-init",
            data: {
                csrfmhub: $('#csrfheaderid').val()
            },
            success: function(jObj) {
                if (jObj.successful) {
                    isInit = true;

                    $.ajax({
                        type: "POST",
                        url: baseUrl + "amazon-connect/ajax-agent-state-change",
                        data: {
                            oldState: oldState,
                            newState: newState,
                            csrfmhub: $('#csrfheaderid').val()
                        },
                        success: function(jObj) {
                            if (jObj.successful) {
                                if (window.opener != null && window.opener.location != null) {
                                    window.opener.location.reload();
                                }
                            }
                        }
                    });
                }
            }
        });
    }

    // Offline
    // Training
    // Available
    // Meeting
    // Break
    // Break (Unpaid)
    // Rework
    // CallingCustomer
    // Busy
    if (newState == "Offline" ||
        newState == "Training" ||
        newState == "Available" ||
        newState == "Meeting" ||
        newState == "Break" ||
        newState == "Break (Unpaid)" ||
        newState == "Rework") {
        $.ajax({
            type: "POST",
            url: baseUrl + "amazon-connect/ajax-agent-state-change",
            data: {
                oldState: oldState,
                newState: newState,
                csrfmhub: $('#csrfheaderid').val()
            },
            success: function(jObj) {
                if (jObj.successful) {
                    if (window.opener != null && window.opener.location != null) {
                        window.opener.location.reload();
                    }
                }
            }
        });
    }

    $.ajax({
        type: "POST",
        url: baseUrl + "amazon-connect/ajax-agent-update-current-status",
        data: {
            newState: newState,
            csrfmhub: $('#csrfheaderid').val()
        }
    });
}

function logout() {
    $.ajax({
        type: "POST",
        url: baseUrl + "amazon-connect/ajax-agent-status-logout",
        data: {
            csrfmhub: $('#csrfheaderid').val()
        },
        success: function(jObj) {
            if (jObj.successful) {
                if (window.opener != null && window.opener.location != null) {
                    window.opener.location.reload();
                }

                //close self as well
                window.close();
            }
        }
    });
}