<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
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
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $this->config->item('mm8_product_html_title'); ?> | Login</title>
        <link href="<?php echo asset_url(); ?>css/bootstrap.min.css" rel="stylesheet" <?php if (SENTRY_ENABLED) { ?>onerror="onCssLoadError(this.href);"<?php }?>>
        <link href="<?php echo asset_url(); ?>font-awesome/css/font-awesome.css" rel="stylesheet" <?php if (SENTRY_ENABLED) { ?>onerror="onCssLoadError(this.href);"<?php }?>>
        <link href="<?php echo asset_url(); ?>fonts/DottiesVanilla.css" rel="stylesheet" <?php if (SENTRY_ENABLED) { ?>onerror="onCssLoadError(this.href);"<?php }?>>
        <link href="<?php echo asset_url(); ?>fonts/Poppins.css" rel="stylesheet" <?php if (SENTRY_ENABLED) { ?>onerror="onCssLoadError(this.href);"<?php }?>>
        <link href="<?php echo asset_url(); ?>css/animate.css" rel="stylesheet" <?php if (SENTRY_ENABLED) { ?>onerror="onCssLoadError(this.href);"<?php }?>>
        <link href="<?php echo cache_buster(asset_url() . 'css/utilihub-hub.min.css'); ?>" rel="stylesheet" <?php if (SENTRY_ENABLED) { ?>onerror="onCssLoadError(this.href);"<?php }?>>
        <link rel="shortcut icon" href="<?php echo asset_url(); ?>favicon.ico"/>
    </head>
    <body class="mhb-skin">
        <?php if (ENVIRONMENT == "production" && !empty($this->config->item('mm8_system_gtmcode'))) { ?>
            <!-- Google Tag Manager (noscript) -->
            <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo $this->config->item('mm8_system_gtmcode'); ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
            <!-- End Google Tag Manager (noscript) -->
        <?php } ?>
