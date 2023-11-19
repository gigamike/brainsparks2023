<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
.fireworks {
  display: none;
  position: absolute;
  top: 0;
  left: 0;
  z-index: 1000;
  height: 100vh;
  width: 100vw;
}
.fireworks canvas {
  top: 0px;
  position: absolute;
  z-index: 1;
}
</style>
<div class="row wrapper">
    <div class="col-sm-12">
        <a href="<?php echo base_url(); ?>therapy" class="m-l-n-sm text-navy">
            <i class="fa fa-angle-left m-r-xs"></i> Therapy
        </a>
    </div>
    <div class="col-sm-12">
        <h3>Facial Expression</h3>
    </div>
</div>
<div class="row wrapper">
    <div class="col-sm-5">
    </div>
    <div class="col-sm-7 text-right">
        <div class="form-group">
            <button id="happyBtn" type="button" class="btn btn-md btn-warning m-b-xs">Happy</button>
            <button id="sadBtn" type="button" class="btn btn-md btn-default m-b-xs">Sad</button>
            <button id="angryBtn" type="button" class="btn btn-md btn-danger m-b-xs">Angry</button>
        </div>
    </div>
</div>
<!-- content wrapper start -->
<div class="row wrapper wrapper-content">
     <div class="trigger_fworks"></div>
     <div class="fireworks">
        <div id="ya-message">Congradutation! You new have completed X.</div>
     </div>

    <div class="row">
        <div class="col-sm-6">
            <div class="text-center">
                <img id="smileyImg" src="<?php echo asset_url(); ?>img/smiley/smiley-happy.jpg" alt="" class="img-rounded">
                <br>
                <strong id="smileyText" style="font-size:32px">Happy</strong>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="text-center">
                <button id="start-camera" class="btn btn-md btn-default m-b-xs">Start Camera</button>
                <button id="click-photo" class="btn btn-md btn-primary m-b-xs hoverme">Click Photo</button>

                <video id="video" width="320" height="240" autoplay></video>
                
                <canvas id="canvas"></canvas>
            </div>
        </div>
    </div>
     <br><br><br>
</div>