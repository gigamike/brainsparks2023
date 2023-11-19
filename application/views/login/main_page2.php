<?php
defined('BASEPATH') or exit('No direct script access allowed');
$this->load->view('login/template_header');
?>
<div class="limiter">
    <div class="container-login100">
        <div class="wrap-login100">
            <form class="login100-form" role="form" method="POST" action="<?php echo base_url(); ?>login/login2/<?php echo $token_str; ?>">
                <input type="hidden" class="txt_csrfname" id="csrfid" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                <div class="row">
                    <div class="col-sm-10 col-sm-offset-1 col-xs-10 col-xs-offset-1 col-lg-8 col-lg-offset-2 m-t-xl">
                        <div class="row m-t-n-xl text-center mobile-only">
                            <div class="col-sm-12">
                                <center><img class="img-responsive" src="<?php echo $this->config->item('hub_login_page_logo_url'); ?>"></center>
                            </div>
                        </div>
                        <div class="widget white-bg p-md text-center m-t-xl">

                            <input type="hidden" name="login_email" value="<?php echo $login_email; ?>">

                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <input type="email" class="form-control input-lg" placeholder="Email" readonly value="<?php echo $login_email; ?>">
                                    </div>
                                    <div class="form-group">
                                        <select class="form-control input-lg" name="login_partner" required>
                                            <option value="" disabled selected>Select Workspace</option>
                                            <?php
                                            foreach ($partners_list as $key => $value) {
                                                $is_selected = set_value('login_partner', '') == $key ? " selected" : "";
                                                echo "<option value=\"$key\" $is_selected>$value</option>";
                                            }
                                            ?>
                                        </select>
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
                            <br/><a href="<?php echo base_url(); ?>register">Don't have an account? Sign up!</a>
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
