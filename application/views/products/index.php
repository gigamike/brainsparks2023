<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="row wrapper">
    <div class="col-sm-5">
        <h3>Products</h3>
    </div>
    <div class="col-sm-7 text-right">
        <div class="form-group">
            <?php echo isset($kb_toggler) ? $kb_toggler : ""; ?>
            <a class="btn btn-md btn-primary m-b-xs" href="<?php echo base_url(); ?>products/add">Add Product</a>
        </div>
    </div>
</div>
<!-- content wrapper start -->
<div class="row wrapper wrapper-content">
    <div class="col-sm-12">

        <?php echo isset($kb_explainer) ? $kb_explainer : ""; ?>

        <div class="ibox float-e-margins white-bg">
            <div class="ibox-content">

                <div class="row">
                    <div class="col-sm-12">
                        <button type="button" id="toggleSearchBtn" class="btn btn-md btn-link datatable-top-togglers"><i class="fa fa-search"></i> <span class="action-label">Search</span></button>
                    </div><!-- column -->
                </div><!-- row -->

                <div class="row wrapper m-t" id="dtSearchContainer" style="display:none;">
                    <div class="col-sm-8 gray-bg p-md">
                        <div class="col-sm-8 m-b-xs">
                            <input type="text" class="form-control" name="dtSearchText" id="dtSearchText" placeholder="Search">
                        </div>
                        <div class="col-sm-4 m-b-xs">
                            <button type="button" id="dtSearchBtn" class="btn btn-primary search-button">Search</button>
                        </div>
                    </div><!-- column -->
                </div><!-- row -->

                <table id="dtProductsTbl" class="table table-responsive">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>U Code</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Date Added</th>
                            <th style="max-width: 100px !important; min-width: 50px !important;">Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>