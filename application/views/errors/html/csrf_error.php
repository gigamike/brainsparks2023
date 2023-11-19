<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
$CFG =& load_class('Config', 'core');
$base_url_temp = $CFG->config['base_url'];
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Restricted Access</title>
        <link href="<?php echo $base_url_temp; ?>assets/css/bootstrap.min.css" rel="stylesheet">
        <link href="<?php echo $base_url_temp; ?>assets/font-awesome/css/font-awesome.css" rel="stylesheet">
        <link href="<?php echo $base_url_temp; ?>assets/css/animate.css" rel="stylesheet">
        <link href="<?php echo $base_url_temp; ?>assets/css/utilihub-hub.min.css" rel="stylesheet">
        <link rel="shortcut icon" href="<?php echo $base_url_temp; ?>assets/favicon.ico"/>
    </head>
    <body class="gray-bg">
        <div class="middle-box text-center loginscreen animated fadeIn">
            <div>
                <div class="m-t-lg"></div>
                <i class="fa fa-lock big-icon"></i>
                <h2><?php echo $heading;?></h2>
                <p><?php echo $message;?></p>
                <a href="<?php echo $base_url_temp . (isset($params) ? $params : ""); ?>" class="btn btn-primary">Home</a>
            </div>
        </div>
        <!-- Mainly scripts -->
        <script src="<?php echo $base_url_temp; ?>assets/js/jquery-3.5.1.min.js"></script>
        <script src="<?php echo $base_url_temp; ?>assets/js/bootstrap.min.js"></script>
    </body>
</html>