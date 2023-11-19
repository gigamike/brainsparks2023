<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta name="viewport" content="width=device-width" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="shortcut icon" href="<?php echo asset_url(); ?>favicon.ico"/>
        <link rel="apple-touch-icon" href="<?php echo asset_url(); ?>apple-touch-icon.png" />
        <link rel="apple-touch-icon" sizes="76x76" href=<?php echo asset_url(); ?>"apple-touch-icon-76x76.png" />
        <link rel="apple-touch-icon" sizes="120x120" href=<?php echo asset_url(); ?>"apple-touch-icon-120x120.png" />
        <link rel="apple-touch-icon" sizes="152x152" href=<?php echo asset_url(); ?>"apple-touch-icon-152x152.png" />
        <title><?php echo $portal_name; ?></title>
        <style type="text/css">
            .hero-text{
                color:#2C83FF;
            }

            @media only screen and (max-width: 640px) {
                h1, h2, h3, h4 {
                    font-weight: 600 !important;
                    margin: 20px 0 5px !important;
                }
                h1 {
                    font-size: 22px !important;
                }
                h2 {
                    font-size: 18px !important;
                }
                h3 {
                    font-size: 16px !important;
                }
                .container {
                    width: 100% !important;
                }
                .content, .content-wrap {
                    padding: 10px !important;
                }
            }

        </style>
    </head>
    <body style="width: 100% !important; height: 100%; line-height: 1.6; background-color: #f6f6f6; margin: 0; padding: 0; font-family: Arial,Helvetica,Verdana,sans-serif; box-sizing: border-box; font-size: 14px;">
        <table style="background-color: #f6f6f6; width: 100%;">
            <tr>
                <td></td>
                <td class="container" width="600" style="display: block !important; max-width: 600px !important; margin: 0 auto !important; clear: both !important;">
                    <div class="content" style="max-width: 600px; margin: 0 auto; display: block; padding: 20px;">
                        <?php if (isset($webview_url) && !empty($webview_url)) { ?>
                            <div style="color: #999;">
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr><td style="font-size: 11px; text-align: center; padding: 20px 20px 20px 20px; line-height: 11px;">If this email is not displayed correctly, <a href="<?php echo $webview_url; ?>" target="_blank">click here</a> to view an online version.</td></tr>
                                </table>
                            </div>
                        <?php } ?>
                        <table style="background: #fff; border: 1px solid #e9e9e9; border-radius: 3px;" width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="padding: 20px;">
                                    <table cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td>
                                                <img style="width: 100%;" alt="<?php echo $portal_name; ?>" title="<?php echo $portal_name; ?>" src="<?php echo $banner; ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 20px 0px 20px 0px;">
                                                <?php echo $contents; ?>
                                            </td>
                                        </tr>

                                        <?php if ((isset($facebook_link) && !empty($facebook_link)) || (isset($twitter_link) && !empty($twitter_link)) || (isset($instagram_link) && !empty($instagram_link)) || (isset($linkedin_link) && !empty($linkedin_link)) || (isset($youtube_link) && empty($youtube_link))) { ?>
                                            <tr>
                                                <td style="color: #FFFFFF; padding: 30px 0px 0px 0px;">
                                                    <table width="100%" cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td width="100%" style="text-align: center; padding: 10px;" valign="middle">
                                                                <?php
                                                                if (isset($facebook_link) && $facebook_link != "") {
                                                                    echo '<a href="' . $facebook_link . '" target="_blank"><img src="' . asset_url() . "html-email/themes/default/img/facebook.png" . '" height="32px" title="Facebook" alt="Facebook"></a>&nbsp;';
                                                                }

                                                                if (isset($twitter_link) && $twitter_link != "") {
                                                                    echo '<a href="' . $twitter_link . '" target="_blank"><img src="' . asset_url() . "html-email/themes/default/img/twitter.png" . '" title="Twitter" alt="Twitter"></a>&nbsp;';
                                                                }

                                                                if (isset($instagram_link) && $instagram_link != "") {
                                                                    echo '<a href="' . $instagram_link . '" target="_blank"><img src="' . asset_url() . "html-email/themes/default/img/instagram.png" . '" title="Instagram" alt="Instagram"></a>&nbsp;';
                                                                }

                                                                if (isset($linkedin_link) && $linkedin_link != "") {
                                                                    echo '<a href="' . $linkedin_link . '" target="_blank"><img src="' . asset_url() . "html-email/themes/default/img/linkedin.png" . '" title="LinkedIn" alt="LinkedIn"></a>&nbsp;';
                                                                }

                                                                if (isset($youtube_link) && $youtube_link != "") {
                                                                    echo '<a href="' . $youtube_link . '" target="_blank"><img src="' . asset_url() . "html-email/themes/default/img/youtube.png" . '" title="Youtube" alt="Youtube"></a>&nbsp;';
                                                                }
                                                                ?>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        <div style="color: #999;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="font-size: 11px; text-align: left; padding: 20px 20px 10px 20px; line-height: 11px;"><?php echo $this->config->item('mm8_system_email_disclaimer'); ?></td>
                                </tr>
                                <tr>
                                    <td style="font-size: 11px; text-align: left; padding: 5px 20px 10px 20px; line-height: 11px;">This email was sent to <?php echo $to_email; ?>.</td>
                                </tr>

                                <?php if(isset($to) && !empty($to)){ ?>
                                <tr>
                                    <td style="font-size: 11px; text-align: center; padding: 20px 20px 10px 20px; line-height: 11px;">
                                        <?php 
                                            $unsubscribe_link = !empty($this->config->item('mhub_apps_alternative_url')) ? $this->config->item('mhub_apps_alternative_url') : $this->config->item('mhub_apps_url') . "unsubscribe/preferences/" . $this->encryption->url_encrypt($to);
                                        ?>
                                        <a href="<?php echo $unsubscribe_link;?>" style="color:#999;text-align:center;">Manage e-mail preferences</a>
                                    </td>
                                </tr>
                                <?php } ?>
                            </table>
                        </div>
                    </div>
                </td>
                <td></td>
            </tr>
        </table>
    </body>
</html>
