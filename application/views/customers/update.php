<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<form id="plateNumberForm">
    <input type="hidden" name="plate_number_id" id="plate_number_id" value="<?php echo $plate_number->id; ?>">

    <div class="row wrapper">
        <div class="col-sm-12">
            <a href="<?php echo base_url() . "plate-numbers"; ?>" class="m-l-n-sm text-navy">
                <i class="fa fa-angle-left m-r-xs"></i> Plate Numbers
            </a>
        </div>
        <div class="col-sm-12">
            <h3>Update Plate Number</h3>
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
                            <label class="control-label">Plate Number<span class="text-danger">*</span></label>
                            <input type="text" name="plate_number" id="plate_number" class="form-control input-lg" value="<?php echo $plate_number->plate_number; ?>" required>
                        </div><!-- column -->
                        <div class="col-sm-6">
                            <?php echo get_kb_field_explainer('plate_number'); ?>
                        </div><!-- column -->
                    </div>

                    <div class="row form-group">
                        <div class="col-sm-6">
                            <label class="control-label">Tracking Type<span class="text-danger">*</span></label>
                            <select class="form-control input-lg" id="tracking_type" name="tracking_type" required>
                                <option value="" disabled selected>Select</option>
                                <option value="hotlist" <?php if ($plate_number->tracking_type == 'hotlist'): ?>selected<?php endif; ?>>Hotlist</option>
                                <option value="whitelist" <?php if ($plate_number->tracking_type == 'whitelist'): ?>selected<?php endif; ?>>Whitelist</option>
                            </select>
                        </div><!-- column -->
                        <div class="col-sm-6">
                            <?php echo get_kb_field_explainer('tracking_type'); ?>
                        </div><!-- column -->
                    </div>

                    <div class="row form-group">
                        <div class="col-sm-6">
                            <label class="control-label">Class<span class="text-danger">*</span></label>
                            <select class="form-control input-lg" id="class" name="class" required>
                                <option value="" disabled selected>Select</option>
                                <option value="private" <?php if ($plate_number->class=='private'): ?>selected<?php endif; ?>>private</option>
                                <option value="public" <?php if ($plate_number->class=='public'): ?>selected<?php endif; ?>>public</option>
                                <option value="government" <?php if ($plate_number->class=='government'): ?>selected<?php endif; ?>>government</option>
                                <option value="diplomat" <?php if ($plate_number->class=='diplomat'): ?>selected<?php endif; ?>>diplomat</option>
                                <option value="other" <?php if ($plate_number->class=='other'): ?>selected<?php endif; ?>>other</option>
                            </select>
                        </div><!-- column -->
                        <div class="col-sm-6">
                            <?php echo get_kb_field_explainer('class'); ?>
                        </div><!-- column -->
                    </div>

                    <div class="row form-group">
                        <div class="col-sm-6">
                            <label class="control-label">Region<span class="text-danger">*</span></label>
                            <select class="form-control input-lg" id="region_id" name="region_id" required>
                                <option value="" disabled selected>Select</option>
                                <?php if (count($regions) > 0): ?>
                                    <?php foreach ($regions as $region): ?>
                                <option value="<?php echo $region->id; ?>" <?php if ($plate_number->region_id==$region->id): ?>selected<?php endif; ?>><?php echo $region->region; ?> (<?php echo $region->region_name; ?>)</option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div><!-- column -->
                        <div class="col-sm-6">
                            <?php echo get_kb_field_explainer('class'); ?>
                        </div><!-- column -->
                    </div>

                    <div class="row form-group">
                        <div class="col-sm-6">
                            <label class="control-label">First Name</label>
                            <input type="text" name="first_name" id="first_name" class="form-control input-lg" value="<?php echo $plate_number->first_name; ?>">
                        </div><!-- column -->
                        <div class="col-sm-6">
                            <?php echo get_kb_field_explainer('first_name'); ?>
                        </div><!-- column -->
                    </div>

                    <div class="row form-group">
                        <div class="col-sm-6">
                            <label class="control-label">Last Name</label>
                            <input type="text" name="last_name" id="last_name" class="form-control input-lg" value="<?php echo $plate_number->last_name; ?>">
                        </div><!-- column -->
                        <div class="col-sm-6">
                            <?php echo get_kb_field_explainer('last_name'); ?>
                        </div><!-- column -->
                    </div>

                    <div class="row form-group">
                        <div class="col-sm-6">
                            <label class="control-label">Address</label>
                            <textarea name="address" id="address" class="form-control input-lg"><?php echo $plate_number->address; ?></textarea>
                        </div><!-- column -->
                        <div class="col-sm-6">
                            <?php echo get_kb_field_explainer('address'); ?>
                        </div><!-- column -->
                    </div>

                    <div class="row form-group">
                        <div class="col-sm-6">
                            <label class="control-label">Last Registration Date</label>
                            <input type="text" name="last_registration_date" id="last_registration_date" class="form-control input-lg datepicker" value="<?php echo $plate_number->last_registration_date; ?>">
                        </div><!-- column -->
                        <div class="col-sm-6">
                            <?php echo get_kb_field_explainer('cr_no'); ?>
                        </div><!-- column -->
                    </div>

                    <div class="row form-group">
                        <div class="col-sm-6">
                            <label class="control-label">CR Number</label>
                            <input type="text" name="cr_no" id="cr_no" class="form-control input-lg" value="<?php echo $plate_number->cr_no; ?>">
                        </div><!-- column -->
                        <div class="col-sm-6">
                            <?php echo get_kb_field_explainer('cr_no'); ?>
                        </div><!-- column -->
                    </div>

                    <div class="row form-group">
                        <div class="col-sm-6">
                            <label class="control-label">Comments<span class="text-danger">*</span></label>
                            <textarea name="comments" id="comments" class="form-control input-lg" required><?php echo $plate_number->comments; ?></textarea>
                        </div><!-- column -->
                        <div class="col-sm-6">
                            <?php echo get_kb_field_explainer('comments'); ?>
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


