<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="row wrapper">
    <div class="col-sm-6">
        <h3>Settings</h3>
    </div>
    <div class="col-sm-6 text-right">
        <div class="form-group">
            <?php echo isset($kb_toggler) ? $kb_toggler : ""; ?>
        </div>
    </div>
</div>
<!-- content wrapper start -->
<div class="row wrapper wrapper-content">
    <?php echo isset($kb_explainer) ? $kb_explainer : ""; ?>
    <div class="col-md-4">
        <div class="ibox float-e-margins" id="General">
            <div class="ibox-title ">
                <h5>General</h5>
            </div>
            <div class="ibox-content p-t-none p-b-none p-r-none p-l-none">
                <ul class="nav">
                    <li>
                        <a href="<?php echo base_url() . 'settings/budget'; ?>">
                            <p class="font-normal">Budget Settings</p>
                        </a>
                    </li>
                </ul>
            </div><!-- ibox content -->
        </div><!-- ibox -->
    </div><!-- col -->
    <div class="col-md-4">
        <div class="ibox float-e-margins" id="Integrations">
            <div class="ibox-title">
                <h5>Integrations</h5>
            </div>
            <div class="ibox-content p-t-none p-b-none p-r-none p-l-none">
            </div><!-- ibox content -->
        </div><!-- ibox -->
    </div><!-- col -->
    <div class="col-md-4">
        <div class="ibox float-e-margins" id="Users">
            <div class="ibox-title ">
                <h5>Users</h5>
            </div>
            <div class="ibox-content p-t-none p-b-none p-r-none p-l-none">
            </div>
        </div>
    </div><!-- col -->
    <div class="col-md-4">
        <div class="ibox float-e-margins" id="Modules">
            <div class="ibox-title ">
                <h5>Modules</h5>
            </div>
            <div class="ibox-content p-t-none p-b-none p-r-none p-l-none">
            </div><!-- ibox content -->
        </div><!-- ibox -->
    </div><!-- column -->
</div>