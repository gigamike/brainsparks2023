<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
$cur_method = $this->router->fetch_method();
$cur_controller = $this->router->fetch_class();
$cur_ruri_string = $this->uri->ruri_string();

$tmp_switch_label = "Switch";

?>
<!-- page-wrapper start -->
<div id="page-wrapper" class="gray-bg">
    <div class="row hidden-xs">
        <nav class="navbar navbar-static-top white-bg m-b-none" role="navigation">
            <div class="navbar-header">
            </div>
            <ul class="nav navbar-top-links navbar-right">
             
                <li>
                    <a href="#" data-toggle="dropdown" class="dropdown-toggle">
                        <h3><?php echo $this->session->utilihub_hub_user_profile_fullname; ?></h3>
                        <span class="text-muted"><?php echo $this->config->item('mm8_agent_roles')[$this->session->utilihub_hub_target_role]; ?></span>
                        <b class="caret"></b>
                    </a>
                    <ul class="dropdown-menu animated fadeInDown m-t-xs">
                        <li><a class="goHomeLink" href="javascript:void(0);">Home</a></li>
                        <li><a href="<?php echo base_url(); ?>profile">Profile</a></li>
                        <li><a href="<?php echo base_url(); ?>login">Logout</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#">
                        <span>
                            <img alt="image" class="img-circle" src="<?php echo $this->session->utilihub_hub_user_profile_photo; ?>" height="37">
                        </span>
                    </a>
                </li>
                <li class="dropdown">
                    <a class="dropdown-toggle count-info" id="support-dropdown-toggle" data-toggle="dropdown" href="#">
                        <i class="text-body fa fa-question-circle-o font-18"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-messages">
                        <li>
                            <a href="<?php echo $this->config->item('mhub_docs_url'); ?>" target="_blank"><div><i class="fa fa-book fa-fw"></i> Knowledge Base</div></a>
                        </li>
                    </ul>
                </li>

                <li class="dropdown b-r-gray mhb-notification">
                    <a class="dropdown-toggle count-info" data-toggle="dropdown" href="#">
                        <i class="fa fa-bell-o font-18"></i>  <span id="notificationTotal" class="label label-primary"></span>
                    </a>
                    <ul class="dropdown-menu dropdown-alerts">
                        <li>
                            <a href="<?php echo "javascript:void(0)"; ?>">
                                <div>
                                    <i class="fa fa-envelope fa-fw"></i> <span id="notificationEmailTotal">No new email</span>
                                    <span style="display: inline" class="pull-right text-muted small" id="notificationEmailDate"></span>
                                </div>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="<?php echo "javascript:void(0)"; ?>">
                                <div>
                                    <i class="fa fa-mobile fa-fw"></i> <span id="notificationSmsTotal">No new sms</span>
                                    <span class="pull-right text-muted small" id="notificationSmsDate"></span>
                                </div>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="javascript:void(0)">
                                <div>
                                    <i class="fa fa-comment fa-fw"></i> <span id="notificationChatTotal">No new message</span>
                                    <span class="pull-right text-muted small" id="notificationChatDate"></span>
                                </div>
                            </a>
                        </li>
                    </ul>
                </li>
                <li id="task-guide-container">
                    <a href="#" id="task-guide-menu" class="right-sidebar-toggle">
                        <h3> <i class="fa fa-info-circle font-18"></i> Task Guide</h3>
                    </a>
                </li>
            </ul>
        </nav>
    </div><!-- row - top right navbar -->

    <!--
    <div class="sidebar-panel">
        <div class="wrapper">
            <i class="fa fa-3x fa-phone" aria-hidden="true"></i>
        </div>
    </div>
    -->
    
    <!-- mobile menu -->
    <div class="row mobile-only" id="mhb-mobile-menu">
        <nav class="navbar navbar-static-top" role="navigation">
            <div class="navbar-header">
                <div class="pull-right mhb-notification">
                    <a href="#">
                        <i class="fa fa-bell-o font-18"></i>
                        <button class="btn btn-primary btn-circle btn-xs">1</button>
                    </a>
                    <button aria-controls="navbar" aria-expanded="false" data-target="#navbar" data-toggle="collapse" class="navbar-toggle collapsed" type="button" style="color: #2C83FF; background-color:transparent;">
                        <i class="fa fa-bars"></i>
                    </button>
                </div>
                <a href="#" class="navbar-brand">
                    <img src="<?php echo base_url(); ?>/assets/img/system-logo.png" width="180"/>
                </a>
            </div>
            <div class="navbar-collapse collapse" id="navbar" aria-expanded="false" style="height: 1px;">
                <ul class="nav navbar-nav">
                    <?php $this->load->view('menu') ?>
                    <li role="separator" class="divider"></li>
                    <li><a class="goHomeLink" href="javascript:void(0);">Home</a></li>
                    <li><a href="javascript:void(0);" onClick="toggle_accounts_menu()"><?php echo $tmp_switch_label; ?></a></li>
                    <!--<li><a href="<?php echo base_url(); ?>profile">Profile</a></li>-->
                    <li><a href="<?php echo base_url(); ?>login">Logout</a></li>
                    <li role="separator" class="divider"></li>
                </ul>
            </div>
        </nav>
    </div>
    <!-- end mobile menu -->

    

    <?php
    if (false) {
        ?>

        <?php $this->load->view('template_secondlevelmenu_' . $this->session->utilihub_hub_target_role . '_header'); ?>

        <div class="row wrapper wrapper-content">
            <div class="col-md-3 hidden-xs">
                <?php
                if ($this->session->utilihub_hub_target_role == USER_SUPER_AGENT &&
                        (
                            ($cur_controller == 'partner' && method_starts_with('settings_')) ||
                        $cur_controller == 'partner_workflow_builder' ||
                        $cur_controller == 'partner_workflow_builder_v2' ||
                        $cur_controller == 'partner_account_manager' ||
                        $cur_controller == 'partner_refer_and_earn' ||
                        $cur_controller == 'partner_my_providers' ||
                        route_starts_with('microsite/') ||
                        route_starts_with('customer_portal_v2/') ||
                        route_starts_with('customer_portal/')
                        )
                ) {
                    $this->load->view('template_secondlevelmenu_1_2');
                } else {
                    $this->load->view('template_secondlevelmenu_' . $this->session->utilihub_hub_target_role);
                } ?>
            </div><!-- Level 2 Menu here -->

            <!-- col main contents -->
            <div class="col-md-9 col-xs-12">
                <?php
    } else {
        // if there is no second level menu, add a spacer
                ?>
                <div class="row m-t-lg">
                    <div class="col-md-12">&nbsp;</div>
                </div>
                <?php
    }?>