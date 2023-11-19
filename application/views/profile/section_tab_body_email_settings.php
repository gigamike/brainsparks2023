<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<form id="userProfileEmailSettingsForm" role="form" enctype="multipart/form-data" method="POST">
    <div class="row">
        <div class="col-sm-6">
            <?php if (isset($user_profile['role']) && ($user_profile['role'] == USER_SUPER_AGENT || $user_profile['role'] == USER_AGENT)) {
    ?>
                <div class="row">
                    <div class="col-sm-12 m-b-sm">
                        <h3>Email Accounts</h3>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 p-md m-t-sm m-b-md gray-bg">
                        <p>The default <strong>From</strong> and <strong>Reply-to</strong> address is used to send emails and track their response in <?php echo $this->config->item('mm8_system_name'); ?>. You can change the <strong>Reply-to</strong> address to receive response in a different mailbox.
                            To ensure that all emails are tracked, make sure that you forward your emails to this address. Don't forget to BCC it as well when replying back to the customer using your mail client.</p>
                        <div class="input-group m-t-md">
                            <input type="text" class="form-control" name="default_ops_reference" id="default_ops_reference" value="<?php echo $partner_data['default_ops_email']; ?>" readonly>
                            <span class="input-group-btn">
                                <button type="button" id="dtSearchBtn" class="btn btn-white btn-radius-sm  copy-to-clipboard" data-clipboard-action="copy" data-clipboard-target="#default_ops_reference">Copy</button>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="form-group<?php
                if (form_error('default_from_email') != "") {
                    echo " has-error";
                } ?>">
                    <label class="control-label">From Email<span class="text-danger">*</span></label>
                    <select class="form-control input-lg" id="default_from_email" name="default_from_email" required>
                        <option value="" disabled selected>Select</option>
                        <?php
                        $tmp_val = set_value('default_from_email', (isset($user_profile['default_from_email']) ? $user_profile['default_from_email'] : ""));
    foreach ($lookup_email_accounts as $value) {
        $selected = $tmp_val != "" && $value == $tmp_val ? " selected" : "";
        echo '<option value="' . $value . '"' . $selected . '>' . $value . '</option>';
    } ?>
                    </select>
    <?php echo form_error('default_from_email'); ?>
                </div>

                <div class="form-group<?php
                     if (form_error('default_reply_to_email') != "") {
                         echo " has-error";
                     } ?>">
                    <label class="control-label">Reply-to Email<span class="text-danger">*</span></label>
                    <select class="form-control input-lg" name="default_reply_to_email" id="default_reply_to_email" required>
                        <option value="" disabled selected>Select</option>
                        <?php
                        $tmp_val = set_value('default_reply_to_email', (isset($user_profile['default_reply_to_email']) ? $user_profile['default_reply_to_email'] : ""));
    foreach ($lookup_email_accounts as $value) {
        $selected = $tmp_val != "" && $value == $tmp_val ? " selected" : "";
        echo '<option value="' . $value . '"' . $selected . '>' . $value . '</option>';
    } ?>
                    </select>
    <?php echo form_error('default_reply_to_email'); ?>
                </div>

                <div class="form-group">
                    <label class="control-label"></label>
                    <a class="btn btn-md btn-info  btn-w-s m-t-xs" href="<?php echo base_url() . 'profile/email/accounts'; ?>">Manage Email Accounts</a>
                </div>
    <?php
}
?>
            <div class="row">
                <div class="col-sm-12 m-t-lg m-b-sm">
                    <h3>Email Signature</h3>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <textarea class="tinymce" name="email_signature" id="email_signature"><?php echo isset($user_profile['email_signature']) ? htmlentities($user_profile['email_signature']) : ""; ?></textarea>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12 m-t-lg">
                    <div class="form-group">
                        <button type="button" id="saveEmailSettingBtn" class="btn btn-md btn-primary btn-w-s m-b-xs">Save</button>
                        <button type="button" id="resetEmailSettingsBtn" class="btn btn-md btn-white btn-w-s m-b-xs">Reset</button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</form>
