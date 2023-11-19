<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<form id="itoolEmailForm">
    <input type="hidden" name="itoolEmailApplication" id="itoolEmailApplication" value="<?php echo $itool_email_application_id; ?>">
    <input type="hidden" name="itoolEmailPartner" id="itoolEmailPartner" value="<?php echo $itool_email_partner_id; ?>">
    <input type="hidden" name="itoolEmailUser" id="itoolEmailUser" value="<?php echo $itool_email_user_id; ?>">

    <!-- FORM ELEMENTS HERE HAVE NO NAME ATTRIBUTE SO THEY WOULDNT BE INCLUDED WHEN SUBMITTED. THIS IS USED FOR TEMPLATING -->
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
    <!-- FORM ELEMENTS HERE HAVE NO NAME ATTRIBUTE SO THEY WOULDNT BE INCLUDED WHEN SUBMITTED. THIS IS USED FOR TEMPLATING -->

    <div class="row">
        <div class="col-md-12 col-sm-12 text-right m-t-n-sm">
            <p><a id="itoolEmailFields" href="javascript:void(0);">Show all fields</a></p>
        </div>
    </div>

    <div id="itoolEmailTemplateWrapper" class="row">
        <div class="col-md-2 col-sm-12">
            <label>Email Template</label>
        </div>
        <div class="col-md-10 col-sm-12">
            <div class="form-group">
                <?php
                echo '<select name="itoolEmailTemplate" id="itoolEmailTemplate" class="form-control input-lg">';
                echo '<option value="" disabled selected>Select</option>';
                foreach ($itool_quicktemplate_data as $key => $value) {
                    echo '<option value="' . $key . '" >' . $value . '</option>';
                }
                echo '</select>';
                ?>
            </div>
        </div>
    </div>

    <div id="itoolEmailFromWrapper" class="row" style="display:none">
        <div class="col-md-2 col-sm-12">
            <label>From</label>
        </div>
        <div class="col-md-10 col-sm-12">
            <div class="form-group">
                <input type="hidden" name="itoolEmailFromName" id="itoolEmailFromName" value="">
                <select name="itoolEmailFrom" id="itoolEmailFrom" class="select2-input form-control input-lg filter-set filter-value" style="width:100%;">
                    <?php foreach ($itool_email_from as $row): ?>
                        <option attr-from-name="<?php echo $row['from_name']; ?>" value="<?php echo $row['email']; ?>" <?php if ($itool_email_from_default == $row['email']): ?>selected<?php endif; ?>><?php echo $row['from_name']; ?> &lt;<?php echo $row['email']; ?>&gt;</option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div id="itoolEmailReplyToWrapper" class="row" style="display:none">
        <div class="col-md-2 col-sm-12">
            <label>Reply-To</label>
        </div>
        <div class="col-md-10 col-sm-12">
            <div class="form-group">
                <select name="itoolEmailReplyTo" id="itoolEmailReplyTo" class="select2-input form-control input-lg filter-set filter-value" style="width:100%;">
                    <?php foreach ($itool_email_reply_to as $row): ?>
                        <option value="<?php echo $row; ?>" <?php if ($itool_email_reply_to_default == $row): ?>selected<?php endif; ?>><?php echo $row; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div id="itoolEmailToWrapper" style="display:none">
        <div class="row">
            <div class="col-md-2 col-sm-12">
                <label>To</label>
            </div>
            <div class="col-md-10 col-sm-12">
                <div class="form-group">
                    <div class="input-group">
                        <input type="email" class="form-control input-lg" name="itoolEmailTo" id="itoolEmailTo" placeholder="To" value="<?php echo isset($itool_email_to) ? $itool_email_to : ''; ?>">
                        <span class="input-group-btn">
                            <button type="button" id="ccLink" class="btn btn-lg btn-white">Cc</button>
                            <button type="button" id="bccLink" class="btn btn-lg btn-white">Bcc</button>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row" id="ccUserAssignedFilterDiv" style="display:none">
            <div class="col-md-2 col-sm-12">
                <label>Cc</label>
            </div>
            <div class="col-md-10 col-sm-12">
                <div class="form-group">
                    <input type="email" class="form-control input-lg" name="ccUserAssignedFilter" id="ccUserAssignedFilter" placeholder="Cc" value="">
                </div>
            </div>
        </div>

        <div class="row" id="bccUserAssignedFilterDiv" style="display:none">
            <div class="col-md-2 col-sm-12">
                <label>Bcc</label>
            </div>
            <div class="col-md-10 col-sm-12">
                <div  class="form-group">
                    <input type="email" class="form-control input-lg" name="bccUserAssignedFilter" id="bccUserAssignedFilter" placeholder="Cc" value="">
                </div>
            </div>
        </div>
    </div>

    <div id="itoolEmailSubjectWrapper" class="row">
        <div class="col-md-2 col-sm-12">
            <label>Subject</label>
        </div>
        <div class="col-md-10 col-sm-12">
            <div class="form-group">
                <input type="text" class="form-control input-lg" name="itoolEmailSubject" id="itoolEmailSubject" placeholder="Subject" value="<?php echo isset($itool_email_subject) ? $itool_email_subject : ''; ?>">
            </div>
        </div>
    </div>

    <div id="itoolEmailAttachmentWrapper" class="row" style="display:none">
        <div class="col-md-2 col-sm-12">
            <label>Attachment</label>
        </div>
        <div class="col-md-10 col-sm-12">
            <div class="form-group">
                <div class="fileinput fileinput-new input-group" data-provides="fileinput">
                    <div class="form-control input-lg" data-trigger="fileinput"><i class="glyphicon glyphicon-file fileinput-exists"></i> <span class="fileinput-filename"></span></div>
                    <span class="input-group-addon btn btn-xs btn-default btn-file"><span class="fileinput-new">Select file</span><span class="fileinput-exists">Change</span><input type="file" class="input-lg" name="itoolEmailAttachment" id="itoolEmailAttachment"></span>
                    <a href="#" class="input-group-addon btn btn-xs btn-default fileinput-exists" data-dismiss="fileinput"><i class="fa fa-times"></i></a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <textarea class="tinymce" name="itoolEmailMessage" id="itoolEmailMessage"><?php echo isset($itool_email_body) ? $itool_email_body : ''; ?></textarea>
        </div>
    </div>

    <br><br>
    <div class="row">
        <div class="col-sm-12 m-b-n-xs text-right">
            <button type="button" class="btn loading-disabler btn-md btn-primary" id="iToolEmailSendBtn">Send</button>
        </div>
    </div>
</form>
