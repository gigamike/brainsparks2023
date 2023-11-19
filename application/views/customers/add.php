<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<form id="customerForm">

    <div class="row wrapper">
        <div class="col-sm-12">
            <a href="<?php echo base_url() . "customers"; ?>" class="m-l-n-sm text-navy">
                <i class="fa fa-angle-left m-r-xs"></i> Customers
            </a>
        </div>
        <div class="col-sm-12">
            <h3>Add Customer</h3>
        </div>
    </div>

    <!-- content wrapper start -->
    <div class="row wrapper wrapper-content">
        <div class="col-sm-12">

            <?php echo isset($kb_explainer) ? $kb_explainer : ""; ?>

            <div class="ibox float-e-margins white-bg">
                <div class="ibox-content">

                    <div class="row form-group">
                        <div class="col-sm-6">
                            <label class="control-label">First Name<span class="text-danger">*</span></label>
                            <input type="text" name="first_name" id="first_name" class="form-control input-lg" value="" required>
                        </div><!-- column -->
                        <div class="col-sm-6">
                            <?php echo get_kb_field_explainer('first_name'); ?>
                        </div><!-- column -->
                    </div>

                    <div class="row form-group">
                        <div class="col-sm-6">
                            <label class="control-label">Last Name<span class="text-danger">*</span></label>
                            <input type="text" name="last_name" id="last_name" class="form-control input-lg" value="" required>
                        </div><!-- column -->
                        <div class="col-sm-6">
                            <?php echo get_kb_field_explainer('last_name'); ?>
                        </div><!-- column -->
                    </div>

                    <div class="row form-group">
                        <div class="col-sm-6">
                            <label class="control-label">Email<span class="text-danger">*</span></label>
                            <input type="text" name="email" id="email" class="form-control input-lg" value="" required>
                        </div><!-- column -->
                        <div class="col-sm-6">
                            <?php echo get_kb_field_explainer('email'); ?>
                        </div><!-- column -->
                    </div>

                    <div class="row form-group">
                        <div class="col-sm-6">
                            <label class="control-label">Mobile Phone<span class="text-danger">*</span></label>
                            <input type="text" name="mobile_phone" id="mobile_phone" class="form-control input-lg" value="" required>
                        </div><!-- column -->
                        <div class="col-sm-6">
                            <?php echo get_kb_field_explainer('mobile_phone'); ?>
                        </div><!-- column -->
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <button id="saveBtn" class="btn btn-md btn-primary btn-w-s m-b-xs" type="button">Save</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</form>


