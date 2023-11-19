<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<form id="itoolSmsForm">
    <input type="hidden" name="userID" id="userID" value="<?php echo $user_id; ?>" >
    <input type="hidden" name="userRole" id="userRole" value="<?php echo $user_role; ?>">
    <input type="hidden" name="activePartner" id="activePartner" value="<?php echo $active_partner; ?>" >
    <input type="hidden" name="activePartnerID" id="activePartnerID" value="<?php echo $active_partner_id; ?>" >
    <input type="hidden" name="applicationID" id="applicationID" value="<?php echo $app_id; ?>" >

    <input type="hidden" name="activeCustomerID" id="activeCustomerID" value="<?php echo isset($application_data['customer_id']) ? $application_data['customer_id'] : ""; ?>">
    <input type="hidden" name="activeCustomerCode" id="activeCustomerCode" value="<?php echo isset($application_data['customer_code']) ? $application_data['customer_code'] : ""; ?>">
    <input type="hidden" name="activeCustomerIdentificationID" id="activeCustomerIdentificationID" value="<?php echo isset($application_data['identification_id']) ? $application_data['identification_id'] : ""; ?>">
    <input type="hidden" name="activeCustomerConcessionID" id="activeCustomerConcessionID" value="<?php echo isset($application_data['concession_id']) ? $application_data['concession_id'] : ""; ?>">

    <!-- FORM ELEMENTS HERE HAVE NO NAME ATTRIBUTE SO THEY WOULDNT BE INCLUDED WHEN SUBMITTED -->
    <input type="hidden" id="itoolSmsApplication" value="<?php echo $itool_sms_application_id; ?>">
    <input type="hidden" id="itoolSmsPartner" value="<?php echo $itool_sms_partner_id; ?>">
    <input type="hidden" id="itoolSmsUser" value="<?php echo $itool_sms_user_id; ?>">
    <!--Form Elements for templateload-->

    <input type="hidden" id="itoolAppFirstName" value="<?php echo isset($itool_app_details['app_first_name']) ? $itool_app_details['app_first_name'] : ''; ?>" />
    <input type="hidden" id="itoolAppPartnerHotline" value="<?php echo isset($itool_app_details['app_partner_hotline']) ? $itool_app_details['app_partner_hotline'] : ''; ?>" />
    <input type="hidden" id="itoolAppRefCode" value="<?php echo isset($itool_app_details['app_ref_code']) ? $itool_app_details['app_ref_code'] : ''; ?>" />
    <input type="hidden" id="itoolAppUserName" value="<?php echo isset($itool_app_details['app_user_name']) ? $itool_app_details['app_user_name'] : ''; ?>"  />
    <input type="hidden" id="itoolAppPortalName" value="<?php echo isset($itool_app_details['app_portal_name']) ? $itool_app_details['app_portal_name'] : ''; ?>"  />
    <input type="hidden" id="itoolAppMoveInDate" value="<?php echo isset($itool_app_details['app_move_in_date']) ? $itool_app_details['app_move_in_date'] : ''; ?>"  />
    <input type="hidden" id="itoolAppFullName" value="<?php echo isset($itool_app_details['app_full_name']) ? $itool_app_details['app_full_name'] : ''; ?>"  />
    <input type="hidden" id="itoolAppNewAddress" value="<?php echo isset($itool_app_details['app_new_address']) ? $itool_app_details['app_new_address'] : ''; ?>"  />
    <input type="hidden" id="itoolAppPartnerName" value="<?php echo isset($itool_app_details['app_partner_name']) ? $itool_app_details['app_partner_name'] : ''; ?>" />


    <div class="row m-t-md">
        <div class="col-md-2 col-sm-12">
            <label>SMS Template</label>
        </div>
        <div class="col-md-10 col-sm-12">
            <div class="form-group">
                <?php
                echo '<select name="itoolSmsTemplate" id="itoolSmsTemplate" class="form-control input-lg">';
                echo '<option value="" disabled selected>Select</option>';
                foreach ($itool_quicktemplate_data as $key => $value) {
                    echo '<option value="' . $key . '" >' . $value . '</option>';
                }
                echo '</select>';
                ?>

            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-2 col-sm-12">
            <label>To</label>
        </div>
        <div class="col-md-10 col-sm-12">
            <div class="form-group">
                <input type="text" class="form-control input-lg" id="itoolSmsTo" placeholder="To" value="<?php
                if (isset($itool_sms_to)) {
                    echo $itool_sms_to;
                }
                ?>">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-2 col-sm-12">
            <label>Message</label>
        </div>
        <div class="col-md-10 col-sm-12">
            <div class="form-group">
                <textarea class="form-control" id="itoolSmsMessage" placeholder="Type your message here..." rows="5" style="resize:vertical;"><?php
                    if (isset($itool_sms_message)) {
                        echo $itool_sms_message;
                    }
                    ?></textarea>
                <small id="itoolSmsMessageCounter" class="pull-right m-b-sm"></small>
                <div class="clearfix m-t-sm"></div>
                <div class="row">
                    <div class="col-sm-6">
                    </div>
                    <div class="col-sm-6">
                        <select name="token" id="token" class="select2-input form-control input-md" style="width:100%;" attr-placeholder="Select to add token">
                            <option value="">Select Token</option>
                            <option value="[PARTNERCODE]">Workspace Code</option>
                            <option value="[PARTNERNAME]">Workspace Name</option>
                            <option value="[PARTNERURL]">Workspace URL</option>
                            <option value="[PARTNERHOTLINE]">Workspace Hotline</option>
                            <option value="[PARTNERSUPPORT]">Workspace Support</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- FORM ELEMENTS HERE HAVE NO NAME ATTRIBUTE SO THEY WOULDNT BE INCLUDED WHEN SUBMITTED -->

    <div class="row">
        <div class="col-sm-12 m-b-n-xs text-right">
            <button type="button" class="btn loading-disabler btn-md btn-primary" id="iToolSmsSendBtn">Send</button>
        </div>
    </div>
</form>
