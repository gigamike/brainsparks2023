<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<script type="text/javascript" src="<?php echo asset_url(); ?>js/jquery-3.5.1.min.js" <?php if (SENTRY_ENABLED) { ?> onerror="onScriptLoadError(this.src);"<?php } ?>></script>
<script type="text/javascript" src="<?php echo asset_url(); ?>js/bootstrap.min.js" <?php if (SENTRY_ENABLED) { ?> onerror="onScriptLoadError(this.src);"<?php } ?>></script>

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
<script type="text/javascript" src="<?php echo asset_url(); ?>js/amazon-connect-v2.0.0/connect-streams-min.js" <?php if (SENTRY_ENABLED) { ?> onerror="onScriptLoadError(this.src);"<?php } ?>></script>
<script type="text/javascript" src="<?php echo cache_buster(asset_url() . 'js/amazon-connect-popup.js'); ?>" <?php if (SENTRY_ENABLED) { ?> onerror="onScriptLoadError(this.src);"<?php } ?>></script>

<?php if (ENVIRONMENT != "development" && !empty($this->config->item('mm8_system_gacode'))) { ?>
    <script>
        (function (i, s, o, g, r, a, m) {
            i['GoogleAnalyticsObject'] = r;
            i[r] = i[r] || function () {
                (i[r].q = i[r].q || []).push(arguments)
            }, i[r].l = 1 * new Date();
            a = s.createElement(o),
                    m = s.getElementsByTagName(o)[0];
            a.async = 1;
            a.src = g;
            m.parentNode.insertBefore(a, m)
        })(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');

        ga('create', '<?php echo $this->config->item('mm8_system_gacode'); ?>', 'auto');
        ga('send', 'pageview');
    </script>
<?php } ?>
<?php if ((defined('SENTRY_ENABLED') && defined('SENTRY_DSN') && defined('SENTRY_RELEASE') && defined('SENTRY_TRACES_SAMPLERATE')) && SENTRY_ENABLED) { ?>
    <script type="text/javascript" src="<?php echo asset_url(); ?>js/sentry-jquery-bindings.js"></script>
<?php } ?>
</body>
</html>
