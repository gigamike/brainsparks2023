<?php
defined('BASEPATH') or exit('No direct script access allowed');
$this->load->view('login/template_header');
?>
<div class="limiter">
    <div class="container-login100">
        <div class="wrap-login100">
            <form class="login100-form" role="form" method="POST" action="<?php echo $action_url_prefix . "1/" . $token1 . "/" . $token2; ?>">
                <input type="hidden" class="txt_csrfname" id="csrfid" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                <div class="row">
                    <div class="col-sm-10 col-sm-offset-1 col-xs-10 col-xs-offset-1 col-lg-8 col-lg-offset-2 m-t-xl">
                        <div class="row m-t-n-xl text-center mobile-only">
                            <div class="col-sm-12">
                                <center><img class="img-responsive" src="<?php echo $this->config->item('hub_login_page_logo_url'); ?>"></center>
                            </div>
                        </div>
                        <div class="widget white-bg">
                            <div class="row">
                                <div class="col-sm-12 m-b-md">
                                    <h1 class="font-bold"><?php echo $hero_html; ?></h1>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 text-muted">
                                    <?php echo $description_html; ?>
                                </div>
                            </div>
                            <?php
                            if ($this->session->flashdata('error_message')) {
                                echo "<div class='alert alert-danger alert-dismissable m-t-sm m-b-sm text-left'>";
                                echo "<button aria-hidden='true' data-dismiss='alert' class='close' type='button'><i class='fa fa-times'></i></button>";
                                echo $this->session->flashdata('error_message');
                                echo "</div>";
                            }
                            ?>
                            <div class="form-group m-t-md">
                                <?php
                                if (!empty($agent_data['email'])) {
                                    $masked_email = preg_replace('/(\S{2})(\S+)@(\S+)/i', '$1******@$3', $agent_data['email']);

                                    echo '<div class="row">';
                                    echo '<div class="col-sm-12">';
                                    echo '<label><input type="radio" name="verifyCodeMethod" value="1" checked/> Email ' . $masked_email . '</label>';
                                    echo '</div>';
                                    echo '</div>';
                                }
                                if (!empty($agent_data['mobile_phone'])) {
                                    $masked_phone = preg_replace('/(\S+)(\S{3})/i', '*******$2', $agent_data['mobile_phone']);

                                    echo '<div class="row">';
                                    echo '<div class="col-sm-12">';
                                    echo '<label><input type="radio" name="verifyCodeMethod" value="2"> SMS ' . $masked_phone . '</label>';
                                    echo '</div>';
                                    echo '</div>';
                                }
                                ?>
                            </div>
                            <button type="submit" class="btn btn-primary btn-radius-sm btn-lg block full-width m-b m-t-md">Continue</button>
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
