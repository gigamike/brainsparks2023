<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!-- content wrapper start -->
<div class="middle-box text-center animated fadeInDown">
    <br/><br/>
    <i class="fa fa-5x text-danger fa-minus-circle"></i>
    <h2 class="text-primary">Something's not right</h2>
    <div class="error-desc m-t-lg">
        <?php
        if (isset($error_str) && $error_str != "") {
            echo $error_str;
        } else {
            echo 'Unfortunately this service is currently unavailable, should you wish to contact us please call <strong>' . $this->config->item('mm8_system_hotline') . '</strong> ' . $this->config->item('mm8_system_support_times') . '.';
        }
        ?>
    </div>
    <br/><br/>
</div>
<!-- content wrapper end -->