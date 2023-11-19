<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<form id="userProfileSecurityForm" role="form" enctype="multipart/form-data" method="POST">
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group<?php
            if (form_error('old_password') != "") {
                echo " has-error";
            }
            ?>">
                <label class="control-label">Old Password<span class="text-danger">*</span></label>
                <input type="password" name="old_password" placeholder="Enter old password" class="form-control input-lg" required="" autocomplete="off">
                <?php echo form_error('old_password'); ?>
            </div>

            <div class="form-group<?php
            if (form_error('new_password') != "") {
                echo " has-error";
            }
            ?>">
                <label class="control-label">New Password<span class="text-danger">*</span></label>
                <input type="password" name="new_password" id="new_password" placeholder="Enter new password" class="form-control input-lg" required="" autocomplete="off">
                <?php echo form_error('new_password'); ?>
            </div>

            <div class="form-group<?php
            if (form_error('new_password_confirm') != "") {
                echo " has-error";
            }
            ?>">
                <label class="control-label">Confirm New Password<span class="text-danger">*</span></label>
                <input type="password" name="new_password_confirm" placeholder="Confirm new password" class="form-control input-lg" required="" autocomplete="off">
                <?php echo form_error('new_password_confirm'); ?>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <p>Your password needs to: (1) include both lower and upper case characters; (2) include at least one number or symbol; and (3) be at least <?php echo $this->config->item('mm8_system_password_length'); ?> characters long</p>
                    <div id="password_meter" class="progress progress-mini m-b-xs">
                        <div style="width:0%;" class="progress-bar progress-bar-info"></div>
                    </div>
                </div>
            </div>


        </div>
    </div>

    <div class="row">
        <div class="col-sm-12 m-t-lg">
            <div class="form-group">
                <button type="button" id="saveSecurityBtn" class="btn btn-md btn-primary btn-w-s m-b-xs">Save</button>
                <button type="button" id="resetSecurityBtn" class="btn btn-md btn-white  btn-w-s m-b-xs">Reset</button>
            </div>
        </div>
    </div>
</form>
