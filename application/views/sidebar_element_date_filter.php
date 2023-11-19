<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div id="<?php echo $prefix; ?>Div" class="date-filter-main-div">
    <label class="font-normal"><?php echo $label; ?></label>
    <select name="<?php echo $prefix; ?>Operator" id="<?php echo $prefix; ?>Operator" attr-target-div="<?php echo $prefix; ?>Div" class="select2-input form-control filter-set filter-operator date-filter-operator" style="width:100%;">
        <?php foreach ($this->config->item('mm8_filter_dates_operator') as $op): ?>
            <option value="<?php echo $op; ?>" <?php if (isset($saved_filter[$prefix . "Operator"]) && $saved_filter[$prefix . "Operator"] == $op): ?>selected<?php endif; ?>><?php echo $this->config->item('mm8_filter_operator_names')[$op]; ?></option>';
        <?php endforeach; ?>
    </select>
    <div class="form-group m-b-xs m-t-xs date-filter-single-input-div"  <?php if (isset($saved_filter[$prefix . "Operator"]) && $saved_filter[$prefix . "Operator"] == QUERY_FILTER_IS_BETWEEN): ?>style="display:none"<?php endif; ?>>
        <div class="input-group date single">
            <span class="input-group-addon border-radius-left"><i class="fa fa-calendar"></i></span><input type="text" name="<?php echo $prefix; ?>" id="<?php echo $prefix; ?>" class="filter-set form-control" placeholder="<?php echo $this->config->item('mm8_global_date_format'); ?>" value="<?php echo isset($saved_filter[$prefix]) ? $saved_filter[$prefix] : ''; ?>">
        </div>
    </div>
    <div class="date-filter-multi-input-div" <?php if (!isset($saved_filter[$prefix . "Operator"]) || isset($saved_filter[$prefix . "Operator"]) && $saved_filter[$prefix . "Operator"] != QUERY_FILTER_IS_BETWEEN): ?>style="display:none"<?php endif; ?>>
        <div class="form-group m-b-xs m-t-xs">
            <div class="input-group date date-from">
                <span class="input-group-addon border-radius-left"><i class="fa fa-calendar"></i></span><input type="text" name="<?php echo $prefix; ?>From" id="<?php echo $prefix; ?>From" attr-target-date="<?php echo $prefix; ?>To" class="filter-set form-control" placeholder="From <?php echo $this->config->item('mm8_global_date_format'); ?>" value="<?php echo isset($saved_filter[$prefix . "From"]) ? $saved_filter[$prefix . "From"] : ''; ?>">
            </div>
        </div>
        <div class="form-group m-b-xs m-t-xs">
            <div class="input-group date date-to">
                <span class="input-group-addon border-radius-left"><i class="fa fa-calendar"></i></span><input type="text" name="<?php echo $prefix; ?>To" id="<?php echo $prefix; ?>To" attr-target-date="<?php echo $prefix; ?>From" class="filter-set form-control" placeholder="To <?php echo $this->config->item('mm8_global_date_format'); ?>" value="<?php echo isset($saved_filter[$prefix . "To"]) ? $saved_filter[$prefix . "To"] : ''; ?>">
            </div>
        </div>
    </div>

</div>
