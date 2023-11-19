<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div id="<?php echo $prefix; ?>Div" class="sidebar-content border-bottom date-filter-main-div">
    <label class="font-normal"><?php echo $label; ?></label>
    <select name="<?php echo $prefix; ?>Operator" id="<?php echo $prefix; ?>Operator" attr-target-div="<?php echo $prefix; ?>Div" class="select2-input form-control filter-set filter-operator date-filter-operator" style="width:100%;">
        <?php
        foreach ($this->config->item('mm8_filter_dates_operator') as $op) {
            echo '<option value="' . $op . '">' . $this->config->item('mm8_filter_operator_names')[$op] . '</option>';
        }
        ?>
    </select>
    <div class="form-group m-b-xs m-t-xs date-filter-single-input-div">
        <div class="input-group date single">
            <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input type="text" name="<?php echo $prefix; ?>" id="<?php echo $prefix; ?>" class="filter-set form-control" placeholder="<?php echo $this->config->item('mm8_global_date_format'); ?>">
        </div>
    </div>
    <div class="date-filter-multi-input-div" style="display:none">
        <div class="form-group m-b-xs m-t-xs">
            <div class="input-group date date-from">
                <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input type="text" name="<?php echo $prefix; ?>From" id="<?php echo $prefix; ?>From" attr-target-date="<?php echo $prefix; ?>To" class="filter-set form-control" placeholder="From <?php echo $this->config->item('mm8_global_date_format'); ?>">
            </div>
        </div>
        <div class="form-group m-b-xs m-t-xs">
            <div class="input-group date date-to">
                <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input type="text" name="<?php echo $prefix; ?>To" id="<?php echo $prefix; ?>To" attr-target-date="<?php echo $prefix; ?>From" class="filter-set form-control" placeholder="To <?php echo $this->config->item('mm8_global_date_format'); ?>">
            </div>
        </div>
    </div>

</div>
