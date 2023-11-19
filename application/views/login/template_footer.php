<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>



<script src="<?php echo asset_url(); ?>js/jquery-3.5.1.min.js" <?php if (SENTRY_ENABLED) { ?> onerror="onScriptLoadError(this.src);"<?php }?>></script>
<script src="<?php echo asset_url(); ?>js/bootstrap.min.js" <?php if (SENTRY_ENABLED) { ?> onerror="onScriptLoadError(this.src);"<?php }?>></script>

<?php if (ENVIRONMENT == "production"): ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script src="<?php echo asset_url(); ?>js/registration.js"></script>
<?php else: ?>
<script src="<?php echo asset_url(); ?>js/registration-without-recaptcha.js"></script>
<?php endif; ?>

<?php if (isset($show_password_meter) && $show_password_meter) {
    ?>
    <script>
        $(document).ready(function () {
            $('#login_password').keyup(function () {
                $('#password_meter').html(checkStrength($('#login_password').val()));
            });

            function checkStrength(password) {
                var strength = 0;
                if (password.length >= <?php echo $this->config->item('mm8_system_password_length'); ?>) {
                    strength += 25;
                }
                if (password.match(/[a-z]/)) {
                    strength += 25;
                }
                if (password.match(/[A-Z]/)) {
                    strength += 25;
                }

                if (password.match(/\d/) || password.match(/[^a-zA-Z\d]/)) {
                    strength += 25;
                }
                return '<div style="width: ' + strength + '%;" class="progress-bar progress-bar-info"></div>';
            }
        });
    </script>
<?php
} ?>
</body>
</html>
