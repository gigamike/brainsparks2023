<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="row wrapper">
    <div class="col-sm-6">
        <?php echo $this->session->utilihub_hub_user_role == USER_MANAGER ? '<h2>Profile</h2>' : '<h3>Profile</h3>'; ?>
    </div>
    <div class="col-sm-6 text-right<?php echo $this->session->utilihub_hub_user_role == USER_MANAGER ? ' m-t-md' : ''; ?>">
        <?php echo isset($kb_toggler) ? $kb_toggler : ""; ?>
        <a class="btn btn-md btn-danger btn-w-md m-b-xs" href="<?php echo base_url(); ?>login">Logout</a>
    </div>
</div>

<!-- content wrapper start -->
<div class="row wrapper wrapper-content">
    <div class="col-sm-12">

        <?php echo isset($kb_explainer) ? $kb_explainer : ""; ?>

        <div class="ibox float-e-margins white-bg">
            <div class="ibox-content">
                <div id="borderlessTabPills">
                    <div class="tabs-container">
                        <ul id="tabHeaders" class="nav nav-tabs">
                            <li class="active"><a id="header-profile" data-toggle="tab" href="#tab-profile" attr-action="profile">Profile</a></li>
                            <li class=""><a id="header-security" data-toggle="tab" href="#tab-security" attr-action="security">Security</a></li>
                            <?php if (isset($user_profile['role']) && ($user_profile['role'] == USER_MANAGER || $user_profile['role'] == USER_SUPER_AGENT || $user_profile['role'] == USER_AGENT)) { ?>
                                <li class=""><a id="header-rewards" data-toggle="tab" href="#tab-rewards" attr-action="rewards">Rewards</a></li>
                                <li class=""><a id="wallet" data-toggle="tab" href="#tab-wallet" attr-action="wallet">Wallet</a></li>
                            <?php } ?>
                            <?php if (isset($user_profile['role']) && ($user_profile['role'] == USER_SUPER_AGENT || $user_profile['role'] == USER_AGENT || $user_profile['role'] == USER_CUSTOMER_SERVICE_AGENT)) { ?>
                                <li class=""><a id="header-email-settings" data-toggle="tab" href="#tab-email-settings" attr-action="email-settings">Email Settings</a></li>
                            <?php } ?>
                            <?php if (isset($email_group_data) && !empty($email_group_data)) { ?>
                                <li class=""><a id="header-email-unsubscribe" data-toggle="tab" href="#tab-email-unsubscribe" attr-action="email-unsubscribe">Email Subscriptions</a></li>
                            <?php } ?>
                        </ul>
                        <div class="tab-content">
                            <div id="tab-profile" attr-action="profile" class="tab-pane active"><div class="panel-body"><?php $this->load->view('profile/section_tab_body_profile'); ?></div></div>
                            <div id="tab-security" attr-action="security" class="tab-pane"><div class="panel-body"><?php $this->load->view('profile/section_tab_body_security'); ?></div></div>
                            <?php if (isset($user_profile['role']) && ($user_profile['role'] == USER_MANAGER || $user_profile['role'] == USER_SUPER_AGENT || $user_profile['role'] == USER_AGENT)) { ?>
                                <div id="tab-rewards" attr-action="rewards" class="tab-pane"><div class="panel-body"><?php $this->load->view('profile/section_tab_body_rewards'); ?></div></div>
                                <div id="tab-wallet" attr-action="wallet" class="tab-pane"><div class="panel-body"><?php $this->load->view('profile/section_tab_body_wallet'); ?></div></div>
                            <?php } ?>
                            <?php if (isset($user_profile['role']) && ($user_profile['role'] == USER_SUPER_AGENT || $user_profile['role'] == USER_AGENT || $user_profile['role'] == USER_CUSTOMER_SERVICE_AGENT)) { ?>
                                <div id="tab-email-settings" attr-action="email-settings" class="tab-pane"><div class="panel-body"><?php $this->load->view('profile/section_tab_body_email_settings'); ?></div></div>
                            <?php } ?>
                            <?php if (isset($email_group_data) && !empty($email_group_data)) { ?>
                                <div id="tab-email-unsubscribe" attr-action="email-unsubscribe" class="tab-pane"><div class="panel-body"><?php $this->load->view('profile/section_tab_body_email_unsubscribe'); ?></div></div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
if ($this->session->flashdata('action_message_failed')) {
    echo '<input type="hidden" id="action_message_failed" value="' . html_escape($this->session->flashdata('action_message_failed')) . '" disabled>';
} elseif ($this->session->flashdata('action_message_success')) {
    echo '<input type="hidden" id="action_message_success" value="' . html_escape($this->session->flashdata('action_message_success')) . '" disabled>';
}
?>

<input type="hidden" id="recommended_length" value="<?php echo $this->config->item('mm8_system_password_length'); ?>" disabled>
<button type="submit" id="hiddenSubmitBtn" hidden></button>
<!--</form>-->
