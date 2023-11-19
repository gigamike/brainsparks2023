<?php
defined('BASEPATH') or exit('No direct script access allowed');
$this->load->view('login/template_header');
?>
<div class="limiter">
    <div class="container-login100">
        <div class="wrap-login100">
            <form class="login100-form" role="form" method="POST" action="<?php echo base_url(); ?>login">
                <input type="hidden" class="txt_csrfname" id="csrfid" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                <input type="hidden" name="redirect" value="<?php echo isset($this->session->utilihub_hub_redirect) ? $this->session->utilihub_hub_redirect : ''; ?>">
                <div class="row">
                    <div class="col-sm-10 col-sm-offset-1 col-xs-10 col-xs-offset-1 col-lg-8 col-lg-offset-2">
                        <div class="row m-t-n-xl text-center mobile-only">
                            <div class="col-sm-12">
                                <center><img class="img-responsive" src="<?php echo $this->config->item('hub_login_page_logo_url'); ?>"></center>
                            </div>
                        </div>
                        <div class="widget white-bg p-md text-center">

                            <?php
                            if ($this->session->flashdata('error_message')) {
                                echo "<div class='alert alert-danger alert-dismissable m-t-sm m-b-md text-left'>";
                                echo "<button aria-hidden='true' data-dismiss='alert' class='close' type='button'><i class='fa fa-times'></i></button>";
                                echo $this->session->flashdata('error_message');
                                echo "</div>";
                            }
                            ?>

                            <div class="row m-b-lg">
                                <img src="<?php echo asset_url(); ?>img/system-logo.png">
                            </div>

                            <?php if ((int) $this->config->item('mm8_hub_account_google_sso') == STATUS_OK) { ?>
                                <div class="row">
                                    <div class="col-sm-12 m-b-xs">
                                        <a href="<?php echo base_url(); ?>login/google-auth" class="btn btn-primary btn-help btn-radius-sm btn-lg block full-width m-b font-bold">Login with Google<i class="fa fa-google text-navy pull-right m-t-xs"></i></a>
                                    </div>
                                </div>

                                <div class="row text-center font-bold text-muted m-b">
                                    or
                                </div>
                            <?php } else { ?>
                                <div class="row">
                                    <div class="col-sm-12 m-b-md">&nbsp;</div>
                                </div>
                            <?php } ?>

                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group<?php
                                    if (form_error('login_email') != "") {
                                        echo " has-error";
                                    }
                                    ?>">
                                        <input type="email" class="form-control input-lg" name="login_email" placeholder="Email" required="" value="<?php echo set_value('login_email', ''); ?>" autocomplete="off">
                                        <?php echo form_error('login_email'); ?>
                                    </div>
                                    <div class="form-group<?php
                                    if (form_error('login_password') != "") {
                                        echo " has-error";
                                    }
                                    ?>">
                                        <input type="password" class="form-control input-lg" name="login_password" placeholder="Password" required="" autocomplete="off">
                                        <?php echo form_error('login_password'); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-12 m-t-sm">
                                    <button type="submit" class="btn btn-primary btn-radius-sm btn-lg block full-width m-b">Login</button>
                                </div>
                            </div>

                            <br>
                            <a href="<?php echo base_url(); ?>login/request-reset">Forgot your username/password?</a>
                            <hr>
                            <a href="<?php echo base_url(); ?>register">Don't have an account? Sign up!</a>
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
