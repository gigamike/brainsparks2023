<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!-- main navigation start -->
<nav class="navbar-default navbar-static-side" role="navigation">
    <div class="sidebar-collapse">
        <ul class="nav" id="side-menu">
            <li class="nav-header">

                <!-- desktop menu -->
                <div class="row" id="full-menu-header">
                    <div class="col-md-8 col-sm-6">
                        <a href="javascript:void(0);" class="goHomeLink"> <img src="<?php echo base_url(); ?>/assets/img/system-logo.png" width="180"/></a>
                    </div>
                    <div class="col-md-2 col-sm-2 pull-right" id="full-menu-header-collapse">
                        <a href="#" class="btn btn-sm btn-white btn-circle navbar-minimalize"><i class="fa fa-angle-left"></i></a>
                    </div>
                </div>

                <div class="logo-element"><img height="40px" src="<?php echo asset_url(); ?>img/system-pin.png"></div>

                <!-- desktop menu end -->
            </li>
            <?php $this->load->view('menu'); ?>
            <li class="nav-header maximize-sidebar-btn">
                <a href="#" class="navbar-minimalize">
                    <span class="btn btn-white btn-circle"><i class="fa fa-angle-right"></i></span>
                </a>
            </li>
            <li class="nav-header maximize-sidebar-btn">
                <a href="#" class="navbar-minimalize navbar-minimalize-text font-normal">
                    Menu
                </a>
            </li>
        </ul>
    </div>
</nav>
<!-- main navigation end -->
