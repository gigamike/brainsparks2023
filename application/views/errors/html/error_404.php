<?php
defined('BASEPATH') or exit('No direct script access allowed');
$CI = & get_instance();
if (!isset($CI)) {
    $CI = new CI_Controller();
}
$CI->load->helper('url');
?><!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Ooopps!</title>
        <link rel="shortcut icon" href="<?php echo base_url(); ?>assets/favicon.ico"/>
        <style type="text/css">

            ::selection { background-color: #E13300; color: white; }
            ::-moz-selection { background-color: #E13300; color: white; }

            body {
                background-color: #fff;
                margin: 40px;
                font: 13px/20px normal Helvetica, Arial, sans-serif;
                color: #4F5155;
            }

            a {
                color: #003399;
                background-color: transparent;
                font-weight: normal;
            }

            h1 {
                color: #444;
                background-color: transparent;
                font-size: 24px;
                font-weight: normal;
                margin: 0 0 0px 0;
                padding: 14px 15px 5px 15px;
            }

            code {
                font-family: Consolas, Monaco, Courier New, Courier, monospace;
                font-size: 12px;
                background-color: #f9f9f9;
                border: 1px solid #D0D0D0;
                color: #002166;
                display: block;
                margin: 14px 0 14px 0;
                padding: 12px 10px 12px 10px;
            }

            #container {
                text-align:center;
                margin: 50px 0px 0px 0px;
            }

            p {
                margin: 12px 15px 12px 15px;
            }

            @media (max-width: 767px) {
                .desktop-only {
                    display: none !important;
                }
            }
            @media (min-width: 767px) {
                .mobile-only {
                    display: none !important;
                }
            }

        </style>
    </head>
    <body>
        <div id="container">
            <br/><br/>
            <img src="<?php echo base_url(); ?>assets/img/system-logo.png" class="img img-responsive">
            <br/><br/>
            <h1><?php echo $heading; ?></h1>
            <?php echo $message; ?>
            <p><a href="<?php echo base_url(); ?>">Home</a></p>
        </div>
    </body>
</html>
