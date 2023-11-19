<?php
defined('BASEPATH') or exit('No direct script access allowed');
$this->load->view('login/template_header');
?>
<div class="limiter">
    <div class="container-login100">
        <div class="wrap-login100">
            <form class="login100-form">

                <div class="row">
                    <div class="col-sm-10 col-sm-offset-1 col-xs-10 col-xs-offset-1 col-lg-8 col-lg-offset-2 m-t-xl">
                        <div class="row m-t-n-xl text-center mobile-only">
                            <div class="col-sm-12">
                                <center><img class="img-responsive" src="<?php echo $this->config->item('hub_login_page_logo_url'); ?>"></center>
                            </div>
                        </div>
                        <div class="widget white-bg p-md m-t-xl">
                            <div class="row">
                                <div class="col-sm-12 m-b-md">
                                    <h1 class="font-bold">Your account is suspended</h1>
                                    <p class="text-muted">If you have any questions, contact us at <a href="mailto:<?php echo $this->config->item('mm8_system_support_email'); ?>"><?php echo $this->config->item('mm8_system_support_email'); ?></a></p>
                                </div>
                            </div>
                        </div>
                        <div class="row m-t-md text-center">
                            <div class="col-sm-12">
                                <center>
                                    <strong>Powered By</strong> <br>
                                    <img src="<?php echo asset_url(); ?>img/system-poweredby.png">
                                </center>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <div class="login100-more">
                <div class="login100-more-mask"></div>
            </div>
        </div>
    </div>
</div>
<?php $this->load->view('login/template_footer'); ?>
