<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
  |--------------------------------------------------------------------------
  | Display Debug backtrace
  |--------------------------------------------------------------------------
  |
  | If set to TRUE, a backtrace will be displayed along with php errors. If
  | error_reporting is disabled, the backtrace will not display, regardless
  | of this setting
  |
 */
defined('SHOW_DEBUG_BACKTRACE') or define('SHOW_DEBUG_BACKTRACE', true);

/*
  |--------------------------------------------------------------------------
  | File and Directory Modes
  |--------------------------------------------------------------------------
  |
  | These prefs are used when checking and setting modes when working
  | with the file system.  The defaults are fine on servers with proper
  | security, but you may wish (or even need) to change the values in
  | certain environments (Apache running a separate process for each
  | user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
  | always be used to set the mode correctly.
  |
 */
defined('FILE_READ_MODE') or define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') or define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE') or define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE') or define('DIR_WRITE_MODE', 0755);

/*
  |--------------------------------------------------------------------------
  | File Stream Modes
  |--------------------------------------------------------------------------
  |
  | These modes are used when working with fopen()/popen()
  |
 */
defined('FOPEN_READ') or define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE') or define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE') or define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE') or define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE') or define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE') or define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT') or define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT') or define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
  |--------------------------------------------------------------------------
  | Exit Status Codes
  |--------------------------------------------------------------------------
  |
  | Used to indicate the conditions under which the script is exit()ing.
  | While there is no universal standard for error codes, there are some
  | broad conventions.  Three such conventions are mentioned below, for
  | those who wish to make use of them.  The CodeIgniter defaults were
  | chosen for the least overlap with these conventions, while still
  | leaving room for others to be defined in future versions and user
  | applications.
  |
  | The three main conventions used for determining exit status codes
  | are as follows:
  |
  |    Standard C/C++ Library (stdlibc):
  |       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
  |       (This link also contains other GNU-specific conventions)
  |    BSD sysexits.h:
  |       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
  |    Bash scripting:
  |       http://tldp.org/LDP/abs/html/exitcodes.html
  |
 */
defined('EXIT_SUCCESS') or define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR') or define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG') or define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE') or define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS') or define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') or define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT') or define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE') or define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN') or define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX') or define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code

//roles
define("MHUB_AGENT", "1"); // movologist
define("MHUB_TEAMLEADER", "2"); // teamleader: previously MHUB_ADMIN
define("MHUB_MANAGER", "3"); // manager: previously MHUB_SUPER
define("MHUB_ADMIN", "4"); // admin
//scope
define("MHUB_INTERNAL", "1");
define("MHUB_EXTERNAL", "2");

//login method
define("USER_LOGIN_BASICAUTH", 1);
define("USER_LOGIN_GOOGLEAUTH", 2);

//mhub user activity
define("ACTIVITY_APPLICATION_ADDED", 1);
define("ACTIVITY_APPLICATION_MODIFIED", 2);
define("ACTIVITY_APPLICATION_SOLD", 3);
define("ACTIVITY_APPLICATION_CLOSED", 4);
define("ACTIVITY_APPLICATION_SHARED", 5);
define("ACTIVITY_APPLICATION_ASSIGNED", 6);
define("ACTIVITY_APPLICATION_RESUBMITTED", 7);
define("ACTIVITY_APPLICATION_VERIFY_SAVED", 8);
define("ACTIVITY_APPLICATION_VERIFY_REJECTED", 9);
define("ACTIVITY_APPLICATION_VERIFY_ACCEPTED", 10);

define("USER_ADMIN", "1");

//provider roles
define("PROVIDER_USER_AGENT", "1");
define("PROVIDER_USER_ADMIN", "2");
define("PROVIDER_USER_SUPER", "3");

// developer roles
define("USER_SUPER_DEV", "1");
define("USER_ADMIN_DEV", "2");
define("USER_DEV", "3");

// dashboard
define("DASHBOARD_DATA_THISWEEK", "1");
define("DASHBOARD_DATA_THISMONTH", "2");
define("DASHBOARD_DATA_THISQUARTER", "3");
define("DASHBOARD_DATA_THISYEAR", "4");
define("DASHBOARD_DATA_LAST7DAYS", "5");
define("DASHBOARD_DATA_LAST30DAYS", "6");
define("DASHBOARD_DATA_LAST12MONTHS", "7");
define("DASHBOARD_DATA_ALLTIME", "10");
define("DASHBOARD_DATA_TODAY", "11");
define("DASHBOARD_DATA_YESTERDAY", "12");
define("DASHBOARD_DATA_LASTWEEK", "13");
define("DASHBOARD_DATA_LASTMONTH", "14");
define("DASHBOARD_DATA_LASTQUARTER", "15");
define("DASHBOARD_DATA_LASTYEAR", "16");
define("DASHBOARD_DATA_THISHOUR", "17");
define("DASHBOARD_DATA_LASTHOUR", "18");
define("DASHBOARD_DATA_NEXTHOUR", "19");

define("DASHBOARD_WIDGET_SUMMARY", "1");
define("DASHBOARD_WIDGET_TABLE", "2");
define("DASHBOARD_WIDGET_BARGRAPH", "3");
define("DASHBOARD_WIDGET_LINEGRAPH", "4");

define("STATUS_OK", 1);
define("STATUS_NG", 0);

define("TRANSACTION_PARTNER", 1);
define("TRANSACTION_AGENT", 2);

//Amazon Connect record
define("AMAZON_CONNECT_CALL_INBOUND", 1);
define("AMAZON_CONNECT_CALL_OUTBOUND", 2);

//errors
define("ERROR_400", "Database Error. Contact your site administrator.");
define("ERROR_401", "Database Error. This application could not be actioned at this time. Contact your site administrator.");
define("ERROR_402", "This application cannot be updated because it is not locked to you.");
define("ERROR_403", "This application cannot be updated because it has already been processed or is currently being processed.");
define("ERROR_404", "No data was retrieved for this application. Contact your site administrator.");
define("ERROR_405", "This application cannot be updated through this Workspace. This application must be updated at the same Workspace where it was created.");
define("ERROR_406", "Internal Error. Could not send email to your email address. Contact your site administrator.");
define("ERROR_407", "There were changes made on an existing customer information and this cannot be updated while there are similar applications waiting to be completed. Wait for a few minutes before actioning again.");
define("ERROR_408", "Your action cannot be completed because you do not have the permission to do so. Contact your site administrator.");
define("ERROR_409", "Account not found. Your account does not exist or it has been deactivated. Contact your site administrator.");
define("ERROR_410", "This link has already expired. The application linked to this may have been recently updated or have already been processed.");
define("ERROR_411", "This application contains prohibited items in the cart. Remove items to continue.");
define("ERROR_412", "Due to no activity, please refresh the page to start again.");
define("ERROR_413", "You already have pending reports. Please try again after a while.");
define("ERROR_414", "Report cannot be retrieved. Contact your site administrator.");
define("ERROR_415", "This application cannot be actioned. Contact your site administrator.");
define("ERROR_416", "You do not have permission to view payment details.");
define("ERROR_417", "This application cannot be actioned because of some missing information. Please check that you have provided all the necessary information.");
define("ERROR_418", "The values you submitted have disallowed characters.");
define("ERROR_419", "Currently SMS feature is disabled. Invalid SMS Provider. Contact your site administrator.");

define("ERROR_501", 'System Error. This application could not be sent at this time. Try again later or contact us.');
define("ERROR_502", 'Internal Error. Contact your site administrator.');
define("ERROR_503", "System Error. Could not mark as read at this time. Contact your site administrator.");
define("ERROR_504", "Internal Error. This application could not be unlocked at this time.");
define("ERROR_505", "'Workspace Data' could not be added on the Report Columns because there is more than one Workspace in your filter.");
define("ERROR_506", "'Provider Data' could not be added on the Report Columns because there is more than one Provider in your filter.");
define("ERROR_507", "Payment status cannot be updated because it is not allowed. Make sure you update it to the correct status.");
define("ERROR_508", "Payment status cannot be updated because the previous status did not match. Hit refresh and update again.");
define("ERROR_509", "Base commission is not set. Contact your site administrator");
define("ERROR_510", "Commission computation error. Contact your site administrator");
define("ERROR_511", "This application cannot be updated because its not yet finalised.");
define("ERROR_512", "System Error. Your request could not be processed at this time, try again later or contact us.");
//for PBX/VOIP configuration in crm
define("ERROR_513", "User device is not properly configured.");
define("ERROR_514", "User device extension is invalid.");
define("ERROR_515", "Commission configuration error. Contact your site administrator");
define("ERROR_516", "This application could not be actioned due to some error. Contact your site administrator (ERROR_516)");
define("ERROR_517", "Failed to process request because Provider Payments has not been finalised.");
define("ERROR_518", "Failed processing your request because application is locked at this time.");
//HUB
define("ERROR_600", "This email address is unavailable, please try again.");
define("ERROR_601", "We're sorry. Something went wrong with the registration process. Please try again or contact us.");
define("ERROR_602", "We're sorry. Something went wrong with the invitation process. Please try again or contact us.");
define("ERROR_603", "These settings cannot be submitted/saved. Contact your site administrator.");
define("ERROR_604", "Payment error. Contact your site administrator.");
define("ERROR_605", "Subscription Error. Please try again or contact us.");
define("ERROR_606", "Billing Error. Please try again or contact us.");
define("ERROR_607", "Action could not be completed because you have reached the limits of your plan. Contact your Admin.");
define("ERROR_608", "Action could not be completed because you have pending issues with your subscription. Contact your Admin.");

define("ERROR_609", "Unable to save the changes due to existing bookings.");
define("ERROR_610", "Error. Select a future date and time.");
define("ERROR_611", "Booking failed. Maximum attendees reached.");
define("ERROR_612", "Status cannot be updated because it is not allowed. Make sure you update it to the correct status.");
define("ERROR_613", "Adding of viewing time not allowed becuase of current inspection status.");
define("ERROR_614", "This email address cannot be used.");

define("ERROR_615", "Action could not be completed because the subscription related to this account has been canceled.");

//api specific
define("ERROR_999", "Data was not processed due to some internal error.");
define("ERROR_998", "Invalid Request Header.");
define("ERROR_1000", "Invalid request.");
define("ERROR_900", "Invalid dataset. Make sure required fields are set.");
define("ERROR_901", "Invalid date. Make sure date fields are properly formatted.");
define("ERROR_902", "Invalid email. Make sure email fields are properly formatted.");
define("ERROR_903", "Invalid plan selection. Make sure selected plans are available.");
define("ERROR_904", "Duplicate record not allowed.");
define("ERROR_905", "No service has been selected. Check previous selections and Select at least one to continue");
define("ERROR_906", "Invalid dataset. PartnerCode is not recognized, incorrect, or inactive.");
define("ERROR_907", "Invalid dataset. WidgetType is not recognized");
define("ERROR_908", "Invalid dataset. Metadata is defined but not properly formatted");
define("ERROR_909", "The current application status does not allow for any updates");
define("ERROR_910", "Invalid dataset. Required fields can not be set to null.");
define("ERROR_911", "Bounce Failed because there's nothing to update.");
define("ERROR_912", "This email is unavailable, please try again.");
define("ERROR_913", "Configuration error. At least one workspace category has to be configured.");
define("ERROR_914", "Access to this Workspace is restricted.");
define("ERROR_915", "Access to this Agent is restricted.");
define("ERROR_916", "Invalid dataset. Agent is invalid.");
define("ERROR_917", "This lead has expired. It might have been processed already or it was removed from the system.");
define("ERROR_918", "Invalid dataset. AffiliateCode is not recognized, incorrect, or inactive.");
define("ERROR_919", "Invalid dataset. Record Identifier is not recognized");
define("ERROR_920", "Duplicate record.");
define("ERROR_921", "You cannot schedule a callback for this application because it has already been processed or is currently being processed.");
define("ERROR_922", "Invalid dataset. WidgetInstance is not recognized");
define("ERROR_923", "No steps found for the given WidgetInstance");
define("ERROR_924", "Invalid dataset. ManagerCode is not recognized, incorrect, or inactive.");
define("ERROR_925", "Duplicate record. PartnerCode is unavailable, please try again.");
define("ERROR_926", "Data update not allowed. You may have provided an invalid dataset.");
define("ERROR_1005", "Missing email and/or password.");
define("ERROR_1036", "Multiple workspaces available. Choose one.");
define("ERROR_1037", "Missing email and/or password and/or workspace.");
define("ERROR_1038", "Invalid email and/or password and/or workspace.");
define("ERROR_1039", "Missing email and/or password.");
define("ERROR_1040", "Invalid email and/or password.");
define("ERROR_1041", "Category Not available for this Workspace.");
define("ERROR_1042", "User is not active.");
define("ERROR_1043", "Error finding active agent data.");
define("ERROR_1044", "Password Reset failed. Try again.");
define("ERROR_1045", "Wrong current password.");
define("ERROR_1046", "Error saving payment details.");
define("ERROR_1047", "Error uploading image for the application");
define("ERROR_1048", "Application does not exist.");
define("ERROR_1049", "Please fill atleast one field.");
define("ERROR_1050", "Invalid resource.");
define("ERROR_1051", "Application Status and Status Tag do not match.");
define("ERROR_1052", "Application Status not allowed.");

define("SUCCESS_1018", "Profile saved successfully.");
define("SUCCESS_1019", "Password updated successfully.");
define("SUCCESS_1020", "Payment details saved successfully.");
define("SUCCESS_1021", "Attachment added successfully.");
define("SUCCESS_1022", "Recommendation sent successfully.");
define("SUCCESS_1023", "Invitation sent successfully.");
define("SUCCESS_1024", "Feedback submitted successfully.");
define("SUCCESS_1025", "Question submitted successfully.");
define("SUCCESS_1026", "Callback booked successfully.");
define("SUCCESS_1027", "Resource request sent successfully.");

//api vault
define("ERROR_950", "No data was retrieved for this application.");

// nps rating
define("NPS_QNS_GRP", 1);
define("SURVEY_QNS_GRP", 2);

// plan pricing
define("STANDARD_METER_TYPE_ELEC", 1);
define("STANDARD_METER_TYPE_GAS", 1);

define("GST_ON_PLAN", 0.10);

//myMarketplace: interaction types
define("MYMP_SEND_MY_DETAILS", 1);
define("MYMP_SEND_ME_DETAILS", 2);

//myMarketplace: listing types
define("MYMP_LISTING_PUBLIC", 1);
define("MYMP_LISTING_PARTNER", 2);
define("MYMP_LISTING_AGENT", 3);

//Notification Module
//Notification type
define("GENERAL_MHUB", 1);  // only for mhub crm notifications
// for app
define("GENERAL_PARTNER", 2);
define("PUSH_PARTNER", 3);
define("ALERT_PARTNER", 4);

// Notification Source Type
define("CRM_NOTIFICATION", 1);
define("PARTNER_NOTIFICATION", 2);

// Nurture / Marketing Campaign Module
define("CAMPAIGN_TYPE_EMAIL", 1);
define("CAMPAIGN_TYPE_SMS", 2);

define("CAMPAIGN_CATEGORY_NURTURE", 1);
define("CAMPAIGN_CATEGORY_MARKETING", 2);

// QA Module
define("QA_PENDING", "0");
define("QA_COMPLETE", "1");

define("QA_PASS", 1);
define("QA_FAIL", 2);
define("QA_NA", 3);
define("QA_TEXT", 4);

define("QTYPE_PF", 1);
define("QTYPE_PFNA", 2);
define("QTYPE_TEXT", 3);

//FLOAT EPSILON
//https://www.php.net/manual/en/language.types.float.php
define("FLOAT_EPSILON", 0.00001);

//db operators
define("QUERY_FILTER_CONTAINS", 1);
define("QUERY_FILTER_NOT_CONTAINS", 2);
define("QUERY_FILTER_EQUALS", 3);
define("QUERY_FILTER_NOT_EQUALS", 4);
define("QUERY_FILTER_GREATER_THAN", 5);
define("QUERY_FILTER_GREATER_THAN_OR_EQUAL", 6);
define("QUERY_FILTER_LESS_THAN", 7);
define("QUERY_FILTER_LESS_THAN_OR_EQUAL", 8);
define("QUERY_FILTER_IS_EMPTY", 9);
define("QUERY_FILTER_IS_NOT_EMPTY", 10);
define("QUERY_FILTER_IS_LISTED", 11);
define("QUERY_FILTER_IS_NOT_LISTED", 12);
define("QUERY_FILTER_IS_BETWEEN", 13);

/*
 * mhub Integration Type
 */

define("MHUB_PROPERTYME", 1);
define("MHUB_FRESHSALES", 2);
define("MHUB_MYDESKTOP", 3);

define("MHUB_BASIC", 1);
define("MHUB_APIKEY", 2);
define("MHUB_BEARERTOKEN", 3);
define("MHUB_OAUTH2", 4);

/*
 * Provider Type
 */
define("PROVIDER_TYPE_MHUB", 1);
define("PROVIDER_TYPE_EXTERNAL", 2);

/*
 * Provider Visibility
 */
define("PROVIDER_VISIBILITY_PUBLIC", 1);
define("PROVIDER_VISIBILITY_PRIVATE", 2);

/*
 * AMS
 */

//AMS User Roles
define("AMS_ROLE_ADMIN", 1);
define("AMS_ROLE_IS", 2);
define("AMS_ROLE_ES", 3);
define("AMS_ROLE_CS", 4);

//AMS Leads Status
define("AMS_LEAD_STATUS_NEW", 1);
define("AMS_LEAD_STATUS_CONTACTED", 2);
define("AMS_LEAD_STATUS_INTERESTED", 3);
define("AMS_LEAD_STATUS_UNDER_REVIEW", 4);
define("AMS_LEAD_STATUS_DEMO", 5);
define("AMS_LEAD_STATUS_CONVERT", 6);
define("AMS_LEAD_STATUS_UNQUALIFIED", 7);

//AMS Lead Source
define("AMS_LEAD_SOURCE_AMS", 1);
define("AMS_LEAD_SOURCE_API", 2);
define("AMS_LEAD_SOURCE_WIDGET", 3);
define("AMS_LEAD_SOURCE_OTHERS", 4);
define("AMS_LEAD_SOURCE_HUB", 5);

define("ERROR_AMS_1", "Invalid dataset. Account Manager is not recognized, incorrect, or inactive.");
define("ERROR_AMS_2", "This email is unavailable, please try again.");
define("ERROR_AMS_3", "Invalid Form Code, please try again.");

//AMS Leads Reason For Contact
define("AMS_LEAD_REASON_FOR_CONTACT_SALES_INQUIRY", 1);
define("AMS_LEAD_REASON_FOR_CONTACT_PLATFORM_DEMO", 2);
define("AMS_LEAD_REASON_FOR_CONTACT_TRAINING", 3);
define("AMS_LEAD_REASON_FOR_CONTACT_OTHERS", 4);

// AMS Workflow Builder Types
define("AMS_WORKFLOW_BUILDER_LEADS_PROFILE_BASED", 1);
define("AMS_WORKFLOW_BUILDER_PARTNER_AGENT_PROFILE_BASED", 2);
define("AMS_WORKFLOW_BUILDER_ACCOUNT_MANAGER_REPORT_BASED", 3);
define("AMS_WORKFLOW_BUILDER_PARTNER_AGENT_REPORT_BASED", 4);

// CRM Workflow Builder Types
define("CRM_WORKFLOW_BUILDER_APPLICATION_BASED", 1);
define("CRM_WORKFLOW_BUILDER_REPORT_BASED", 2);

// AMS Tasks Status
define("AMS_TASK_STATUS_OPEN", 1);
define("AMS_TASK_STATUS_COMPLETED", 2);
define("AMS_TASK_STATUS_NOT_REQUIRED", 3);
define("AMS_TASK_STATUS_CANCELLED", 4);
define("AMS_TASK_STATUS_RESCHEDULED", 5);

// AMS Tasks Type
define("AMS_TASK_TYPE_CALL", 1);
define("AMS_TASK_TYPE_FOLLOW_UP", 2);
define("AMS_TASK_TYPE_EMAIL", 3);
define("AMS_TASK_TYPE_MEETING", 4);
define("AMS_TASK_TYPE_POST", 5);
define("AMS_TASK_TYPE_TRAINING", 6);

// AMS Tasks Repeat
define("AMS_TASK_REPEAT_NONE", 0);
define("AMS_TASK_REPEAT_EVERY_DAY", 1);
define("AMS_TASK_REPEAT_EVERY_WEEK", 2);
define("AMS_TASK_REPEAT_EVERY_MONTH", 3);
define("AMS_TASK_REPEAT_EVERY_YEAR", 4);
define("AMS_TASK_REPEAT_EVERY_CUSTOM", 5);

// AMS Tasks Repeat Custom
define("AMS_TASK_REPEAT_CUSTOM_DAILY", 1);
define("AMS_TASK_REPEAT_CUSTOM_WEEKLY", 2);
define("AMS_TASK_REPEAT_CUSTOM_MONTHLY", 3);
define("AMS_TASK_REPEAT_EVERY_YEARLY", 4);

// AMS Sendy
define("AMS_SENDY_ACTIVE_ADMINS", 1);
define("AMS_SENDY_INACTIVE_ADMINS", 2);
define("AMS_SENDY_ACTIVE_CAMPAIGN_ADMINS", 3);
define("AMS_SENDY_INACTIVE_CAMPAIGN_ADMINS", 4);
define("AMS_SENDY_ACTIVE_SALES_AGENTS", 5);
define("AMS_SENDY_INACTIVE_SALES_AGENTS", 6);
define("AMS_SENDY_PROSPECTING", 7);

/*
 * CONNECT
 */

define("CONNECT_USER_TABLE_TBL_PARTNER_AGENTS", 1);
define("CONNECT_USER_TABLE_TBL_USER", 2);
define("CONNECT_USER_TABLE_TBL_CUSTOMER", 3);
define("CONNECT_USER_TABLE_TBL_USER_MARKETPLACE", 4);
define("CONNECT_USER_TABLE_TBL_ACCOUNT_MANAGER_LEADS", 5);
define("CONNECT_USER_TABLE_TBL_CONNECT_CHAT_GUESTS", 6);
define("CONNECT_USER_TABLE_TBL_CUSTOMER_PROFILE", 7);

define("CONNECT_USER_GROUP_ADMIN", 1);
define("CONNECT_USER_GROUP_CAMPAIGN_ADMIN_CONNECTIONS_PLUS", 2);
define("CONNECT_USER_GROUP_CAMPAIGN_ADMIN_CONNECTIONS", 3);
define("CONNECT_USER_GROUP_SALES_AGENT_CONNECTIONS_PLUS", 4);
define("CONNECT_USER_GROUP_SALES_AGENT_CONNECTIONS", 5);
define("CONNECT_USER_GROUP_CUSTOMER_SERVICE_AGENT_CONNECTIONS", 6);
define("CONNECT_USER_GROUP_CRM_USER", 7);
define("CONNECT_USER_GROUP_CRM_TEAMLEADER", 8);
define("CONNECT_USER_GROUP_CRM_MANAGER", 9);
define("CONNECT_USER_GROUP_CUSTOMER", 10);
define("CONNECT_USER_GROUP_MARKETPLACE_MANAGER", 11);
define("CONNECT_USER_GROUP_MARKETPLACE_ADMIN", 12);
define("CONNECT_USER_GROUP_MARKETPLACE_AGENT", 13);
define("CONNECT_USER_GROUP_AMS_ADMIN", 14);
define("CONNECT_USER_GROUP_AMS_INTERNAL_SALES", 15);
define("CONNECT_USER_GROUP_AMS_EXTERNAL_SALES", 16);
define("CONNECT_USER_GROUP_CUSTOMER_PROFILE", 17);
define("CONNECT_USER_GROUP_AMS_CLIENT_SUCCESS", 18);

define("CONNECT_TICKET_STATUS_OPEN", 1);
define("CONNECT_TICKET_STATUS_PENDING", 2);
define("CONNECT_TICKET_STATUS_RESOLVED", 3);
define("CONNECT_TICKET_STATUS_CLOSED", 4);

define("CONNECT_TICKET_URGENCY_LOW", 1);
define("CONNECT_TICKET_URGENCY_MEDIUM", 2);
define("CONNECT_TICKET_URGENCY_HIGH", 3);

define("CONNECT_TICKET_IMPACT_LOW", 1);
define("CONNECT_TICKET_IMPACT_MEDIUM", 2);
define("CONNECT_TICKET_IMPACT_HIGH", 3);

define("CONNECT_TICKET_PRIORITY_LOW", 1);
define("CONNECT_TICKET_PRIORITY_MEDIUM", 2);
define("CONNECT_TICKET_PRIORITY_HIGH", 3);
define("CONNECT_TICKET_PRIORITY_URGENT", 4);

//CONNECT TICKET Source
define("CONNECT_TICKET_SOURCE_CONNECT", 1);
define("CONNECT_TICKET_SOURCE_API", 2);
define("CONNECT_TICKET_SOURCE_WIDGET", 3);
define("CONNECT_TICKET_SOURCE_OTHERS", 4);
define("CONNECT_TICKET_SOURCE_EMAIL", 5);
define("CONNECT_TICKET_SOURCE_AMAZON_CONNECT", 6);

define("ERROR_CONNECT_1", "Invalid dataset. Ticket category is not recognized, incorrect, or inactive.");
define("ERROR_CONNECT_2", "Invalid dataset. User ID is not recognized, incorrect, or inactive.");
define("ERROR_CONNECT_3", "Invalid Form Code, please try again.");
define("ERROR_CONNECT_4", "Invalid dataset. Subject is a required field.");
define("ERROR_CONNECT_5", "Invalid dataset. Description is a required field.");
define("ERROR_CONNECT_6", "Invalid dataset. Status is not recognized or incorrect.");
define("ERROR_CONNECT_7", "Invalid dataset. Urgency is not recognized or incorrect.");
define("ERROR_CONNECT_8", "Invalid dataset. Impact is not recognized or incorrect.");
define("ERROR_CONNECT_9", "Invalid dataset. Priority is not recognized or incorrect.");
define("ERROR_CONNECT_10", "Invalid dataset. Workspace is not recognized or incorrect.");
define("ERROR_CONNECT_11", "Invalid dataset. User is not recognized or incorrect.");

define("CONNECT_APP_GROUP_HUB", 1);
define("CONNECT_APP_GROUP_CRM", 2);
define("CONNECT_APP_GROUP_DASHBOARD", 3);
define("CONNECT_APP_GROUP_MARKETPLACE", 4);
define("CONNECT_APP_GROUP_CUSTOMER", 5);
define("CONNECT_APP_GROUP_AMS", 6);
define("CONNECT_APP_GROUP_AMS_PUBLIC", 7);
define("CONNECT_APP_GROUP_CAMPAIGN_PUBLIC", 8);
define("CONNECT_APP_GROUP_CUSTOMER_PORTAL", 9);

define("CONNECT_CHANNEL_TYPE_AMS_DEFAULT", 1);
define("CONNECT_CHANNEL_TYPE_CRM_PARTNER", 2);
define("CONNECT_CHANNEL_TYPE_HUB_DEFAULT", 3);
define("CONNECT_CHANNEL_TYPE_CAMPAIGN_PRIVATE", 4);
define("CONNECT_CHANNEL_TYPE_APPLICATION", 5);
define("CONNECT_CHANNEL_TYPE_AMS_LEADS", 6);
define("CONNECT_CHANNEL_TYPE_AMS_PUBLIC", 7);
define("CONNECT_CHANNEL_TYPE_CAMPAIGN_PUBLIC", 8);
define("CONNECT_CHANNEL_TYPE_CUSTOMER_PORTAL", 9);
define("CONNECT_CHANNEL_TYPE_AMS_DEFAULT_CS", 10);

/*
 * DOCS
 */
define("DOCS_STATUS_PUBLISHED", 1);
define("DOCS_STATUS_DRAFT", 2);

// SES
define("SES_BLACKLIST_TYPE_BOUNCE", 1);
define("SES_BLACKLIST_TYPE_COMPLAINT", 2);
define("SES_BLACKLIST_TYPE_TESTDATA", 3);
define("SES_BLACKLIST_TYPE_OTHER", 4);

define("SES_BOUNCE_PERMANENT", 1);
define("SES_BOUNCE_TRANSIENT", 2);

/*
 * CRM USER STATUS
 */
define("USER_STATUS_ACTIVE", 1);
define("USER_STATUS_ACTIVE_PLAY", 2);
define("USER_STATUS_BREAK", 3);
define("USER_STATUS_TRAINING", 4);
define("USER_STATUS_MEETING", 5);
define("USER_STATUS_IDLE", 6);
define("USER_STATUS_OFFLINE", 7); // Amazon Connect Only
define("USER_STATUS_BREAK_UNPAID", 8); // Amazon Connect Only
define("USER_STATUS_REWORK", 9); // Amazon Connect Only

/*
 * COLOR BASED ON INSPINIA
 */
define("INSPINIA_HEX_SUCCESS", "#136DBC");
define("INSPINIA_HEX_PRIMARY", "#2C83FF");
define("INSPINIA_HEX_DANGER", "#B63737");
define("INSPINIA_HEX_WARNING", "#E59A16");
define("INSPINIA_HEX_INFO", "#00EDC7");
define("INSPINIA_HEX_DEFAULT", "#545A5F");

/*
 * QA REVIEW RESULT TYPES
 */
define("QA_REVIEW_PASS", 1);
define("QA_REVIEW_FAIL", 2);

/*
 * HUBSPOT constants
 */
define('HUBSPOT_REGISTERED_CONNECTIONS', 'mh_registered_connections');
define('HUBSPOT_UNVERIFIED_CONNECTIONS', 'mh_unverified_connections');
define('HUBSPOT_VERIFIED_CONNECTIONS', 'mh_verified_connections');
define('HUBSPOT_DEREGISTERED_CONNECTIONS', 'mh_deregistered_connections');

define('HUBSPOT_REGISTERED_AFFILIATES', 'mh_registered_affiliates');
define('HUBSPOT_UNVERIFIED_AFFILIATES', 'mh_unverified_affiliates');
define('HUBSPOT_VERIFIED_AFFILIATES', 'mh_verified_affiliates');
define('HUBSPOT_DEREGISTERED_AFFILIATES', 'mh_deregistered_affiliates');

define('HUBSPOT_REGISTERED_PROVIDERS', 'mh_registered_providers');
define('HUBSPOT_UNVERIFIED_PROVIDERS', 'mh_unverified_providers');
define('HUBSPOT_VERIFIED_PROVIDERS', 'mh_verified_providers');
define('HUBSPOT_DEREGISTERED_PROVIDERS', 'mh_deregistered_providers');

/*
 * Email subscription categories
 */
define("EMAIL_SUBSCRIPTION_MARKETING", 1);
define("EMAIL_SUBSCRIPTION_REPORTS", 2);
define("EMAIL_SUBSCRIPTION_SYSTEM_MAINTENANCE", 3);
define("EMAIL_SUBSCRIPTION_MOVING_NOTIFICATIONS", 4);
define("SMS_SUBSCRIPTION_MARKETING", 1);
define("SMS_SUBSCRIPTION_REPORTS", 2);
define("SMS_SUBSCRIPTION_SYSTEM_MAINTENANCE", 3);
define("SMS_SUBSCRIPTION_MOVING_NOTIFICATIONS", 4);

/*
 * Email subscription status
 */
define("EMAIL_SUBSCRIPTION_UNKNOWN", 0);
define("EMAIL_SUBSCRIPTION_OPTEDIN", 1);
define("EMAIL_SUBSCRIPTION_OPTEDOUT", 2);


/*
 * CONNECT SD
 */
define("CONNECT_SD_USER_TYPE_USER_ADMIN", 1);
define("CONNECT_SD_USER_TYPE_USER_AGENT", 2);

define("CONNECT_SD_APP_DASHBOARD", 1);
define("CONNECT_SD_APP_HUB", 2);
define("CONNECT_SD_APP_PROVIDER", 3);
define("CONNECT_SD_APP_CUSTOMER_PORTAL", 4);
define("CONNECT_SD_APP_CUSTOMER_PORTAL_V2", 5);
define("CONNECT_SD_APP_PUBLIC_WEBSITE", 6);
define("CONNECT_SD_APP_CONNECT_SD", 7);

define("CONNECT_SD_TICKET_STATUS_OPEN", 1);
define("CONNECT_SD_TICKET_STATUS_PENDING", 2);
define("CONNECT_SD_TICKET_STATUS_RESOLVED", 3);
define("CONNECT_SD_TICKET_STATUS_CLOSED", 4);
define("CONNECT_SD_TICKET_STATUS_REOPEN", 5);

define("CONNECT_SD_TICKET_URGENCY_LOW", 1);
define("CONNECT_SD_TICKET_URGENCY_MEDIUM", 2);
define("CONNECT_SD_TICKET_URGENCY_HIGH", 3);

define("CONNECT_SD_TICKET_IMPACT_LOW", 1);
define("CONNECT_SD_TICKET_IMPACT_MEDIUM", 2);
define("CONNECT_SD_TICKET_IMPACT_HIGH", 3);

define("CONNECT_SD_TICKET_PRIORITY_LOW", 1);
define("CONNECT_SD_TICKET_PRIORITY_MEDIUM", 2);
define("CONNECT_SD_TICKET_PRIORITY_HIGH", 3);
define("CONNECT_SD_TICKET_PRIORITY_CRITICAL", 4);

define("CONNECT_SD_CHAT_CHANNEL_STATUS_ACTIVE_PENDING_REPLY_FROM_CUSTOMER", 1);
define("CONNECT_SD_CHAT_CHANNEL_STATUS_ACTIVE_PENDING_REPLY_FROM_AGENT", 2);
define("CONNECT_SD_CHAT_CHANNEL_STATUS_INACTIVE", 3);
define("CONNECT_SD_CHAT_CHANNEL_STATUS_ARCHIVE", 4);

define("CONNECT_SD_USER_STATUS_ONLINE", 1);
define("CONNECT_SD_USER_STATUS_OFFLINE", 2);
define("CONNECT_SD_USER_STATUS_BREAK", 3);
define("CONNECT_SD_USER_STATUS_TRAINING", 4);
define("CONNECT_SD_USER_STATUS_MEETING", 5);
