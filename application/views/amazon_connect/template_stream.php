<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style type="text/css">
    .btn-circle.btn-lg {
      width: 50px;
      height: 25px;
      padding: 2px 2px;
      font-size: 10px;
      line-height: 1.33;
      border-radius: 25px;
    }

    .btn-record.btn-lg {
      width: 50px;
      height: 25px;
      padding: 2px 2px;
      font-size: 10px;
      line-height: 1.33;
      border-radius: 5px;
    }

    .btn-record-recording{
        background-color: #B63737;
        border-color: #B63737;
        color: #fff;
    }
</style>
<div>
    <span id="amazonConnectLoginMessage">
        <div class="row">
            <div class="col-sm-12">
                <div id="amazonConnectLoginMessage" class="alert alert-danger" role="alert">
                    Please login to <a onclick="self.close()" href="<?php echo $this->config->item('mm8_amazon_connect_access_url'); ?>/ccp-v2/" target="_blank">Amazon Connect</a> then refresh the page.
                </div>
            </div>
        </div>
    </span>

    <div id="container-div-amazon-connect" style="width: 320px !important; height: 520px !important; display: none;"></div>
      
</div>