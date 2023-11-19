<?php defined('BASEPATH') or exit('No direct script access allowed'); ?><!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate, max-stale=0, post-check=0, pre-check=0" />
        <meta http-equiv="pragma" content="no-cache">
        <meta http-equiv="expires" content="-1">
        <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7; IE=EmulateIE9">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="description" content="">
        <meta name="author" content="">
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <meta name="mobile-web-app-capable" content="yes" />
        <title><?php echo $this->config->item('mm8_product_html_title'); ?><?php echo isset($user_sudo_role) && isset($target_id) && isset($user_access[$user_sudo_role][$target_id]) ? " | " . $user_access[$user_sudo_role][$target_id] : ""; ?></title>
        <link href="<?php echo asset_url(); ?>css/bootstrap.min.css" rel="stylesheet">
        <link href="<?php echo asset_url(); ?>font-awesome/css/font-awesome.css" rel="stylesheet">
        <link href="<?php echo asset_url(); ?>fonts/DottiesVanilla.css" rel="stylesheet">
        <link href="<?php echo asset_url(); ?>fonts/Poppins.css" rel="stylesheet">
        <link href="<?php echo asset_url(); ?>css/utilihub-sprites/style.css" rel="stylesheet">
        <link href="<?php echo asset_url(); ?>css/plugins/toastr/toastr.min.css" rel="stylesheet">
        <link href="<?php echo asset_url(); ?>css/plugins/select2/select2.css" rel="stylesheet">
        <link href="<?php echo asset_url(); ?>css/plugins/select2/select2-bootstrap.css" rel="stylesheet">
        <link href="<?php echo asset_url(); ?>css/plugins/iCheck/line/line.css" rel="stylesheet">
        <link href="<?php echo asset_url(); ?>css/plugins/iCheck/square/blue.css" rel="stylesheet">
        <link href="<?php echo asset_url(); ?>css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
        <?php
        if (isset($styles)) {
            foreach ($styles as $css) {
                echo '<link href="' . cache_buster($css) . '" rel="stylesheet"';
                if (SENTRY_ENABLED) {
                    echo ' onerror="onCssLoadError(this.href);"';
                }
                echo '>' . PHP_EOL;
            }
        }
        ?>
        <link href="<?php echo asset_url(); ?>css/animate.css" rel="stylesheet">
        <link href="<?php echo cache_buster(asset_url() . 'css/inspinia.min.css'); ?>" rel="stylesheet" <?php if (SENTRY_ENABLED) { ?> onerror="onCssLoadError(this.href);"<?php } ?>>
        <link rel="shortcut icon" href="<?php echo asset_url(); ?>favicon.ico"/>
        <link rel="apple-touch-icon" href="<?php echo asset_url(); ?>apple-touch-icon.png">
        <link rel="apple-touch-icon" sizes="76x76" href="<?php echo asset_url(); ?>apple-touch-icon-76x76.png">
        <link rel="apple-touch-icon" sizes="120x120" href="<?php echo asset_url(); ?>apple-touch-icon-120x120.png">
        <link rel="apple-touch-icon" sizes="152x152" href="<?php echo asset_url(); ?>apple-touch-icon-152x152.png">
        <script type="text/javascript">
            var baseUrl = '<?php echo base_url(); ?>'; var baseCountry = '<?php echo $this->config->item('mm8_country_code'); ?>';  var baseCurrency = '<?php echo $this->config->item('mm8_currency'); ?>'; var systemName = '<?php echo $this->config->item('mm8_system_name'); ?>'; var systemPrefix = '<?php echo $this->config->item('mm8_system_prefix'); ?>';var baseDateFormat = '<?php echo $this->config->item('mm8_global_date_format'); ?>';
            var currentController = '<?php echo $this->router->fetch_class(); ?>';
            var currentMethod = '<?php echo $this->router->fetch_method(); ?>';
            var systemName = '<?php echo $this->config->item('mm8_system_name'); ?>';
            var awsRegion = '<?php echo $this->config->item('mm8_amazon_connect_aws_region'); ?>'; var amazonConnectCCPURL = '<?php echo $this->config->item('mm8_amazon_connect_access_url'); ?>';
        </script>
        <?php echo isset($internal_css) && !empty($internal_css) ? $internal_css : ""; ?>
        <link href="<?php echo asset_url(); ?>css/amazon-connect/style.min.css" rel="stylesheet">
    </head>
    <body<?php echo!empty($this->session->utilihub_hub_global_view_options['main_menu_collapsed']) ? ' class="' . $this->session->utilihub_hub_global_view_options['main_menu_collapsed'] . ' mhb-skin"' : ' class="mhb-skin"'; ?><?php echo isset($onload_call) ? ' onload="' . $onload_call . '"' : ''; ?>>
        <input type="hidden" class="txt_csrfname" id="csrfheaderid" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
        <!-- wrapper start -->
        <div id="wrapper">
