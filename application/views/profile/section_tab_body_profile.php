<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<form id="userProfileForm" role="form" enctype="multipart/form-data" method="POST">
    <div class="row">
        <div class="col-sm-6">

            <div class="form-group<?php
            if (form_error('first_name') != "") {
                echo " has-error";
            }
            ?>">
                <label class="control-label">First Name<span class="text-danger">*</span></label>
                <input type="text" name="first_name" placeholder="First Name" class="form-control input-lg" value="<?php echo set_value('first_name', (isset($user_profile['first_name']) ? $user_profile['first_name'] : "")); ?>" required autocomplete="off">
                <?php echo form_error('first_name'); ?>
            </div>

            <div class="form-group<?php
            if (form_error('last_name') != "") {
                echo " has-error";
            }
            ?>">
                <label class="control-label">Last Name<span class="text-danger">*</span></label>
                <input type="text" name="last_name" placeholder="Last Name" class="form-control input-lg" value="<?php echo set_value('last_name', (isset($user_profile['last_name']) ? $user_profile['last_name'] : "")); ?>" required autocomplete="off">
                <?php echo form_error('last_name'); ?>
            </div>

            <div class="form-group<?php
            if (form_error('user_email') != "") {
                echo " has-error";
            }
            ?>">
                <label class="control-label">Email<span class="text-danger">*</span></label>
                <input type="email" name="user_email" id="user_email" placeholder="Email" class="form-control input-lg" value="<?php echo set_value('user_email', (isset($user_profile['email']) ? $user_profile['email'] : "")); ?>" required autocomplete="off">
                <?php echo form_error('user_email'); ?>
            </div>
            <?php
            $active_phone_preference = 'mobile';

            if (isset($user_profile['preferred_phone_number']) && $user_profile['preferred_phone_number'] == 'mobile') {
                $active_phone_preference = 'mobile';
            } elseif (isset($user_profile['preferred_phone_number']) && $user_profile['preferred_phone_number'] == 'office') {
                $active_phone_preference = 'office';
            }
            ?>

            <div class="form-group<?php
            if (form_error('mobile_phone') != "") {
                echo " has-error";
            }
            ?>">
                <label class="control-label">Preferred Phone Number</label>
                <div class="radio">
                    <label>
                        <input type="radio" name="preferred_phone_number"
                               class="preferred_phone_number"
                               value="mobile"
                               <?php echo $active_phone_preference == 'mobile' ? 'checked="checked"' : ""; ?>
                               > Mobile
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="radio" name="preferred_phone_number"
                               class="preferred_phone_number"
                               value="office"
                               <?php echo $active_phone_preference == 'office' ? 'checked="checked"' : ""; ?>
                               > Office (with extension)
                    </label>
                </div>
            </div>

            <div class="form-group<?php
            if (form_error('mobile_phone') != "") {
                echo " has-error";
            }
            ?>" id="mobile-phone-section"
                 style="<?php echo $active_phone_preference == 'office' ? 'display:none;' : ''; ?>"
                 >
                <label class="control-label">Mobile Phone<span class="text-danger">*</span></label>
                <input type="text" name="mobile_phone" placeholder="Mobile Phone" class="form-control input-lg" value="<?php echo set_value('mobile_phone', (isset($user_profile['mobile_phone']) ? $user_profile['mobile_phone'] : "")); ?>"
                <?php echo $active_phone_preference == 'mobile' ? ' required' : ''; ?>
                       autocomplete="off">
                       <?php echo form_error('mobile_phone'); ?>
            </div>

            <div class="form-group<?php
            if (form_error('office_phone') != "") {
                echo " has-error";
            }
            ?>" id="office-phone-section"  style="<?php echo $active_phone_preference == 'mobile' ? 'display:none;' : ''; ?>">
                <label class="control-label">Office Phone<span class="text-danger">*</span></label>
                <input type="text" name="office_phone" placeholder="Office Phone" class="form-control input-lg" value="<?php echo set_value('office_phone', (isset($user_profile['office_phone']) ? $user_profile['office_phone'] : "")); ?>"
                       <?php echo $active_phone_preference == 'office' ? ' required' : ''; ?> autocomplete="off">
                       <?php echo form_error('office_phone'); ?>
            </div>

            <div class="form-group<?php
            if (form_error('office_extension') != "") {
                echo " has-error";
            }
            ?>" id="office-extension-section" style="<?php echo $active_phone_preference == 'mobile' ? 'display:none;' : ''; ?>">
                <label class="control-label">Office Extension</label>
                <input type="text" name="office_extension" placeholder="Office Extension" class="form-control input-lg" value="<?php echo set_value('office_extension', (isset($user_profile['office_extension']) ? $user_profile['office_extension'] : "")); ?>" autocomplete="off">
                <?php echo form_error('office_extension'); ?>
            </div>

            <div class="form-group<?php
            if (form_error('position') != "") {
                echo " has-error";
            }
            ?>">
                <label class="control-label">Position</label>
                <input type="text" name="position" placeholder="E.g Property Manager" class="form-control input-lg" value="<?php echo set_value('position', (isset($user_profile['position']) ? $user_profile['position'] : "")); ?>" autocomplete="off">
                <?php echo form_error('position'); ?>
            </div>


            <?php if (in_array($this->session->utilihub_hub_user_role, [USER_SUPER_AGENT, USER_AGENT])) { ?>

                <div class="form-group<?php
                if (form_error('microsite_id') != "") {
                    echo " has-error";
                }
                ?>">
                    <label class="control-label">Microsite ID</label>
                    <input type="text" name="microsite_id" placeholder="firstname-lastname" class="form-control input-lg" value="<?php echo set_value('microsite_id', (isset($user_profile['microsite_id']) ? $user_profile['microsite_id'] : "")); ?>" autocomplete="off">
                    <?php echo form_error('microsite_id'); ?>
                </div>
            <?php } ?>

            <div class="form-group<?php
            if (form_error('agent_about') != "") {
                echo " has-error";
            }
            ?>">
                <label class="control-label">About you</label>
                <textarea name="agent_about" rows=4 class="form-control input-lg tinymce" autocomplete="off"><?php echo set_value('agent_about', (isset($user_profile['about']) ? $user_profile['about'] : "")); ?></textarea>
                <?php echo form_error('agent_about'); ?>
            </div>

            <div class="form-group">
                <label class="control-label">Profile Photo</label><br>
                <!-- Profile Photo -->
                <div class="fileinput fileinput-new" data-provides="fileinput">
                    <div class="fileinput-new thumbnail" style="width: 100px; height: 100px;">
                        <img src="<?php echo $this->session->utilihub_hub_user_profile_photo; ?>" alt="..."/>
                    </div>
                    <div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 110px; max-height: 110px;"></div>
                    <div>
                        <span class="btn btn-default btn-xs btn-file"><span class="fileinput-new">Select image</span><span class="fileinput-exists">Change</span><input type="file" name="profile_photo"></span>
                        <a href="#" class="btn btn-xs btn-default fileinput-exists" data-dismiss="fileinput">Remove</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <div class="row">
        <div class="col-sm-12 m-t-lg">
            <div class="form-group">
                <button type="button" id="saveProfileBtn" class="btn btn-md btn-primary btn-w-s m-b-xs">Save</button>
                <button type="button" id="resetProfileBtn" class="btn btn-md btn-white btn-w-s m-b-xs">Reset</button>
            </div>
        </div>
    </div>
</form>
