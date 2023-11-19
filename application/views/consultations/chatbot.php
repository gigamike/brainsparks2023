<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

 <div class="row wrapper">
    <div class="col-sm-12">
        <a href="<?php echo base_url(); ?>consultations" class="m-l-n-sm text-navy">
            <i class="fa fa-angle-left m-r-xs"></i> Consultations
        </a>
    </div>
    <div class="col-sm-12">
        <h3>Dr Ausome</h3>
    </div>
</div>

<!-- content wrapper start -->
<div class="row wrapper wrapper-content">
    <div class="col-sm-12">
        <?php echo isset($kb_explainer) ? $kb_explainer : ""; ?>
        <div class="ibox float-e-margins white-bg">
            <div class="ibox-content">
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-primary">
                            <div class="panel-heading">
                               
                            </div>
                            <div class="panel-body" style="height:400px" id="messagesWrapper">
                                
                            </div>
                            <div class="panel-footer">
                                <form id="chatForm">
                                    <div class="input-group">
                                        <input id="message" name="message" type="text" class="form-control input-sm" placeholder="Type your message here..." />
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-primary btn-sm" id="sendBtn">
                                                Send</button>
                                        </span>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>