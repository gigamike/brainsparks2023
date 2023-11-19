<?php defined('BASEPATH') or exit('No direct script access allowed'); ?><!DOCTYPE html>
<html>
    <head>
        <?php if (ENVIRONMENT == "production" && !empty($this->config->item('mm8_system_gtmcode'))) { ?>
            <!-- Google Tag Manager -->
            <script>
                (function (w, d, s, l, i) {
                    w[l] = w[l] || [];
                    w[l].push({'gtm.start': new Date().getTime(), event: 'gtm.js'});
                    var f = d.getElementsByTagName(s)[0], j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : '';
                    j.async = true;
                    j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
                    f.parentNode.insertBefore(j, f);
                })(window, document, 'script', 'dataLayer', '<?php echo $this->config->item('mm8_system_gtmcode'); ?>');
            </script>
            <!-- End Google Tag Manager -->
        <?php } ?>
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
        <link href="<?php echo asset_url(); ?>css/bootstrap.min.css" rel="stylesheet" <?php if (SENTRY_ENABLED) { ?>onerror="onCssLoadError(this.href);"<?php } ?>>
        <link href="<?php echo asset_url(); ?>font-awesome/css/font-awesome.css" rel="stylesheet" <?php if (SENTRY_ENABLED) { ?>onerror="onCssLoadError(this.href);"<?php } ?>>
        <link rel="shortcut icon" href="<?php echo asset_url(); ?>favicon.ico"/>
        <link rel="apple-touch-icon" href="<?php echo asset_url(); ?>apple-touch-icon.png">
        <link rel="apple-touch-icon" sizes="76x76" href="<?php echo asset_url(); ?>apple-touch-icon-76x76.png">
        <link rel="apple-touch-icon" sizes="120x120" href="<?php echo asset_url(); ?>apple-touch-icon-120x120.png">
        <link rel="apple-touch-icon" sizes="152x152" href="<?php echo asset_url(); ?>apple-touch-icon-152x152.png">
        <script type="text/javascript">var baseUrl = '<?php echo base_url(); ?>'; var baseCountry = '<?php echo $this->config->item('mm8_country_code'); ?>';  var baseCurrency = '<?php echo $this->config->item('mm8_currency'); ?>'; var systemName = '<?php echo $this->config->item('mm8_system_name'); ?>'; var systemPrefix = '<?php echo $this->config->item('mm8_system_prefix'); ?>';var baseDateFormat = '<?php echo $this->config->item('mm8_global_date_format'); ?>';</script>
    </head>
    <body>
        <?php if (ENVIRONMENT == "production" && !empty($this->config->item('mm8_system_gtmcode'))) { ?>
            <!-- Google Tag Manager (noscript) -->
            <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo $this->config->item('mm8_system_gtmcode'); ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
            <!-- End Google Tag Manager (noscript) -->
        <?php } ?>
        <!-- wrapper start -->
        <div class="container-fluid">
