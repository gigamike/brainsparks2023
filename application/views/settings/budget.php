<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<form id="budgetForm" role="form" method="POST">
    <div class="row wrapper">
        <div class="col-sm-12">
            <div class="row">
                <div class="col-xs-12">
                    <a href="<?php echo base_url(); ?>settings" class="m-l-n-sm text-navy">
                        <i class="fa fa-angle-left m-r-xs"></i> Settings
                    </a>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6  col-xs-8">
                    <h3>Budget Threshold</h3>
                </div>
                <div class="col-sm-6  col-xs-4 text-right">
                    <div class="form-group">
                        <?php echo isset($kb_toggler) ? $kb_toggler : ""; ?>
                    </div>
                </div>
            </div><!-- row -->
        </div><!-- column -->
    </div>
    <!-- content wrapper start -->
    <div class="row wrapper wrapper-content">
        <div class="col-sm-12">
            <?php echo isset($kb_explainer) ? $kb_explainer : ""; ?>
            <div class="ibox float-e-margins white-bg">
                <div class="ibox-content">
                    <div class="row form-group">
                        <div class="col-sm-6">
                            <label class="control-label">Amount<span class="text-danger">*</span></label>
                            <input type="text" id="amount" name="amount" placeholder="Amount" class="form-control input-lg" value="<?php echo $budget_threshold->value; ?>" required>
                            <span class="help-block m-b-none">Actual amount spent may vary.</span>
                        </div><!-- column -->
                        <div class="col-sm-6">
                            <?php echo get_kb_field_explainer('budget.amount'); ?>
                        </div><!-- column -->
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <button type="button" id="saveBtn" class="btn btn-md btn-primary  btn-w-sm m-b-xs">Save</button>
                    <button type="reset" id="resetBtn" class="btn btn-md btn-white  btn-w-sm m-b-xs">Reset</button>
                </div>
            </div>
        </div>
    </div>
</form>
<br><br><br>