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
        <title><?php echo(isset($this->config->item('mm8_product_badge')[ENVIRONMENT]) ? $this->config->item('mm8_product_badge')[ENVIRONMENT] . " | " : ""); ?><?php echo $this->config->item('mm8_product_html_title'); ?> - Dialler</title>
        <link href="<?php echo asset_url(); ?>css/bootstrap.min.css" rel="stylesheet" <?php if (SENTRY_ENABLED) { ?>onerror="onCssLoadError(this.href);"<?php } ?>>
        <link href="<?php echo asset_url(); ?>font-awesome/css/font-awesome.css" rel="stylesheet" <?php if (SENTRY_ENABLED) { ?>onerror="onCssLoadError(this.href);"<?php } ?>>
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
        <!--<link href="<?php echo asset_url(); ?>css/animate.css" rel="stylesheet" <?php if (SENTRY_ENABLED) { ?>onerror="onCssLoadError(this.href);"<?php } ?>>-->
        <link href="<?php echo asset_url(); ?>css/utilihub-crm.min.css" rel="stylesheet" <?php if (SENTRY_ENABLED) { ?>onerror="onCssLoadError(this.href);"<?php } ?>>
        <link rel="shortcut icon" href="<?php echo asset_url(); ?>favicon.ico"/>
        <link rel="apple-touch-icon" href="<?php echo asset_url(); ?>apple-touch-icon.png">
        <link rel="apple-touch-icon" sizes="76x76" href="<?php echo asset_url(); ?>apple-touch-icon-76x76.png">
        <link rel="apple-touch-icon" sizes="120x120" href="<?php echo asset_url(); ?>apple-touch-icon-120x120.png">
        <link rel="apple-touch-icon" sizes="152x152" href="<?php echo asset_url(); ?>apple-touch-icon-152x152.png">
        <script type="text/javascript">var baseUrl = '<?php echo base_url(); ?>'; var baseCountry = '<?php echo $this->config->item('mm8_country_code'); ?>';  var baseCurrency = '<?php echo $this->config->item('mm8_currency'); ?>'; var systemName = '<?php echo $this->config->item('mm8_system_name'); ?>'; var systemPrefix = '<?php echo $this->config->item('mm8_system_prefix'); ?>'; var baseDateFormat = '<?php echo $this->config->item('mm8_global_date_format'); ?>'; var systemName = '<?php echo $this->config->item('mm8_system_name'); ?>';var awsRegion = '<?php echo $this->config->item('mm8_amazon_connect_aws_region'); ?>';var amazonConnectCCPURL = '<?php echo $this->config->item('mm8_amazon_connect_access_url'); ?>';</script>
    </head>

    <body id="mainBodyDiv" class="md-skin" style="overflow-x:hidden;overflow-y:hidden;">
        <?php if (ENVIRONMENT == "production" && !empty($this->config->item('mm8_system_gtmcode'))) {
            ?>
            <!-- Google Tag Manager (noscript) -->
            <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo $this->config->item('mm8_system_gtmcode'); ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
            <!-- End Google Tag Manager (noscript) -->
            <?php
        }
        ?>
        <input type="hidden" class="txt_csrfname" id="csrfheaderid" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">