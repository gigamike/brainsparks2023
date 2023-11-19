<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<form id="bulkImportForm">

    <div class="row wrapper">
        <div class="col-sm-12">
            <a href="<?php echo base_url() . "customers"; ?>" class="m-l-n-sm text-navy">
                <i class="fa fa-angle-left m-r-xs"></i> Customers
            </a>
        </div>
        <div class="col-sm-12">
            <h3>Bulk Customer with purchase history</h3>
        </div>
    </div>

    <!-- content wrapper start -->
    <div class="row wrapper wrapper-content">
        <div class="col-sm-12">

            <?php echo isset($kb_explainer) ? $kb_explainer : ""; ?>

            <div class="ibox float-e-margins white-bg">
                <div class="ibox-content">

                    <div class="form-group">
                        <label class="col-sm-2 control-label">Import Customers<span class="text-danger">*</span></label>
                        <div class="col-sm-10">
                            <div class="tabs-container">
                                <ul class="nav nav-tabs">
                                    <li class="active"><a data-toggle="tab" href="#tab-1"> Text</a></li>
                                    <li class=""><a data-toggle="tab" href="#tab-2">CSV</a></li>
                                </ul>
                                <div class="tab-content">
                                    <div id="tab-1" class="tab-pane active">
                                        <div class="panel-body">
                                            <textarea id="text" name="text" class="form-control" rows="20" placeholder="<?php echo htmlspecialchars($text_placeholder); ?>"></textarea>
                                            <p class="help-block"><strong>Format:</strong> <?php echo $text_format; ?></p>
                                        </div>
                                    </div>
                                    <div id="tab-2" class="tab-pane">
                                        <div class="panel-body">
                                            <input id="csv" name="csv" type="file">
                                            <p class="help-block"><strong>Format:</strong><br> <?php echo $csv_format; ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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


