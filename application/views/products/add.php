<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<form id="productFrm">

    <div class="row wrapper">
        <div class="col-sm-12">
            <a href="<?php echo base_url() . "products"; ?>" class="m-l-n-sm text-navy">
                <i class="fa fa-angle-left m-r-xs"></i> Products
            </a>
        </div>
        <div class="col-sm-12">
            <h3>Add Product</h3>
        </div>
    </div>

    <!-- content wrapper start -->
    <div class="row wrapper wrapper-content">
        <div class="col-sm-12">

            <?php echo isset($kb_explainer) ? $kb_explainer : ""; ?>

            <div class="ibox float-e-margins white-bg">
                <div class="ibox-content">

                    <div class="form-group">
                        <label class="control-label">Logo</label><br>

                        <div class="fileinput fileinput-new" data-provides="fileinput">
                            <div class="fileinput-new thumbnail" style="width: 100px; height: 100px;">
                            </div>
                            <div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 110px; max-height: 110px;"></div>
                            <div>
                                <span class="btn btn-default btn-xs btn-file"><span class="fileinput-new">Select image</span><span class="fileinput-exists">Change</span><input type="file" name="photo"></span>
                                <a href="#" class="btn btn-xs btn-default fileinput-exists" data-dismiss="fileinput">Remove</a>
                            </div>
                        </div>
                        
                    </div>

                    <div class="row form-group">
                        <div class="col-sm-6">
                            <label class="control-label">Name<span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control input-lg" value="" required>
                        </div><!-- column -->
                        <div class="col-sm-6">
                            <?php echo get_kb_field_explainer('name'); ?>
                        </div><!-- column -->
                    </div>

                    <div class="row form-group">
                        <div class="col-sm-6">
                            <label class="control-label">Price<span class="text-danger">*</span></label>
                            <input type="text" name="price" id="price" class="form-control input-lg" value="" required>
                        </div><!-- column -->
                        <div class="col-sm-6">
                            <?php echo get_kb_field_explainer('price'); ?>
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


