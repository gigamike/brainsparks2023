<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
$cur_method = $this->router->fetch_method();
$cur_controller = $this->router->fetch_class();
$cur_ruri_string = $this->uri->ruri_string();
?>

<?php
if (false) {
    ?>
    </div><!-- end col main contents -->
    </div><!-- end row from template_submenu -->
<?php
} ?>

<!-- footer start -->
<div class="row mobile-only"><div class="col-sm-12">&nbsp;</div></div>
<div class="footer">
    <div class="row desktop-only">
        <div class="col-sm-4 m-t-xs"><a href="#" target="_blank">Terms</a> | <a href="#" target="_blank">Privacy</a></div>
        <div class="col-sm-4 text-center">
            <strong>Powered By</strong><br>
            <img src="<?php echo asset_url(); ?>img/system-poweredby.png">
        </div>
        <div class="col-sm-4 text-right m-t-xs">&copy; <?php echo date('Y'); ?></div>
    </div>
    <div class="row mobile-only text-center">
        <div class="col-sm-12"><img src="<?php echo asset_url(); ?>img/system-poweredby.png"></div>
        <div class="col-sm-12"><a href="#" target="_blank">Terms</a> | <a href="#" target="_blank">Privacy</a> | &copy; <?php echo date('Y'); ?></div>
    </div>
</div>
<!-- footer end -->


<!-- right toolbar start -->
<?php $this->load->view('template_sidebarmenu'); ?>
<!-- right toolbar end -->

</div>
<!-- page-wrapper end -->
</div>
<!-- wrapper end -->

<!-- Spinner Modal-->
<div class="modal inmodal" id="spinnerModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-sm text-center" style="margin-top:200px;"><i class="fa fa-refresh fa-spin fa-5x text-white"></i></div>
</div>
<!-- Spinner Modal-->


<!-- Spinner Modal-->
<div class="modal inmodal" id="spinnerModal2" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false"></div>
<!-- Spinner Modal-->


<!-- Anchors for dynamic modals-->
<div id="modalAnchorDiv"></div>
<!-- Anchors for dynamic modals-->

<!-- Snippet Modal-->
<div id="widgetSnippetContainer"></div>
<!-- Snippet Modal-->

<!-- Knowledge Base -->
<?php $this->load->view('knowledge_base/kb_embed_modal'); ?>
<!-- Knowledge Base -->

<?php // $this->load->view('amazon_connect/login_modal');?>

<script type="text/javascript" src="<?php echo asset_url(); ?>js/jquery-3.5.1.min.js"></script>
<script type="text/javascript" src="<?php echo asset_url(); ?>js/bootstrap.min.js"></script>
<script type="text/javascript" src="<?php echo cache_buster(asset_url() . 'js/plugins/toastr/toastr.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo cache_buster(asset_url() . 'js/plugins/metisMenu/jquery.metisMenu.js'); ?>"></script>
<script type="text/javascript" src="<?php echo cache_buster(asset_url() . 'js/plugins/slimscroll/jquery.slimscroll.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo cache_buster(asset_url() . 'js/plugins/select2/select2.js'); ?>"></script>
<script type="text/javascript" src="<?php echo cache_buster(asset_url() . 'js/plugins/iCheck/icheck.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo cache_buster(asset_url() . 'js/plugins/sweetalert/sweetalert.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo cache_buster(asset_url() . 'js/plugins/validate/jquery.validate.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo cache_buster(asset_url() . 'js/plugins/wow/wow.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo cache_buster(asset_url() . 'js/plugins/velocity/velocity.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo cache_buster(asset_url() . 'js/plugins/velocity/velocity.ui.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo cache_buster(asset_url() . 'js/plugins/jquery.resize.js'); ?>"></script>
<script type="text/javascript" src="<?php echo cache_buster(asset_url() . 'js/plugins/tinymce/tinymce.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo cache_buster(asset_url() . 'js/plugins/tinymce/jquery.tinymce.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo cache_buster(asset_url() . 'js/hub.js'); ?>"></script>
<script type="text/javascript" src="<?php echo cache_buster(asset_url() . 'js/amazon-connect.js'); ?>"></script>
<script type="text/javascript" src="<?php echo cache_buster(asset_url() . 'js/inspinia.js'); ?>"></script>
<script type="text/javascript" src="<?php echo cache_buster(asset_url() . 'js/plugins/pace/pace.min.js'); ?>"></script>
<?php
if (isset($scripts)) {
        foreach ($scripts as $js) {
            echo '<script type="text/javascript" src="' . cache_buster($js) . '"';
            if (SENTRY_ENABLED) {
                echo ' onerror="onScriptLoadError(this.src);"';
            }
            echo '></script>' . PHP_EOL;
        }
    }
?>

</body>
</html>
