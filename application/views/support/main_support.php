<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<form id="userSupportForm" class="form-horizontal" role="form">
    <div class="row wrapper border-bottom white-bg">
        <div class="col-sm-8 m-t-md">
            <h3>
                <?php echo $main_navigator_title; ?> <i class="fa fa-angle-right"></i>
                <a href="#" class="text-navy">Support</a>
            </h3>
        </div>
        <div class="col-sm-4 m-t-sm text-right">
            <div class="form-group">
                <?php echo isset($kb_toggler) ? $kb_toggler : ""; ?>
                <button type="button" id="resetBtn" class="btn btn-md btn-white  btn-w-md m-t-xs m-l-xs">Reset</button>
                <button type="button" id="saveBtn" class="btn btn-md btn-primary  btn-w-md m-t-xs m-l-xs">Save</button>
            </div>
        </div>
    </div>
    <!-- content wrapper start -->
    <div class="row wrapper wrapper-content">
        <div class="col-sm-12">

            <?php echo isset($kb_explainer) ? $kb_explainer : ""; ?>

            <div class="ibox float-e-margins">
                <div class="ibox-title bordered-box-title">
                    <h5>Create Ticket</h5>
                    <div class="ibox-tools">
                        <a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                    </div>
                </div>
                <div class="ibox-content bordered-box">
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Subject<span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <input type="text" name="subject" id="subject" placeholder="Subject" class="form-control input-lg" required>
                        </div>
                    </div>
                    <div class="hr-line-dashed desktop-only"></div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Description<span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <textarea name="description" id="description" placeholder="Description" rows="10" class="form-control input-lg" required></textarea>
                        </div>
                    </div>
                    <div class="hr-line-dashed desktop-only"></div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Priority</label>
                        <div class="col-sm-9">
                            <select class="form-control input-lg" name="priority" id="priority" required>
                                <option value="" disabled selected>Select Priority</option>
                                <option value="1">Low</option>
                                <option value="2">Medium</option>
                                <option value="3">High</option>
                                <option value="4">Priority</option>
                            </select>
                        </div>
                    </div>

                </div>
            </div>


        </div>
    </div>
</form>
