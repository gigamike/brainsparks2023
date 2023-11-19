<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta name="viewport" content="width=device-width" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title><?php echo $this->config->item('mm8_product_name'); ?> by <?php echo $this->config->item('mm8_system_name'); ?></title>
    </head>
    <body>
        <table width="100%">
            <tr><td style="display: block !important; margin: 0 auto !important; clear: both !important;"><?php echo $message; ?></td></tr>
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
    </body>
</html>
