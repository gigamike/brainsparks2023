<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="ibox">
    <div class="ibox-content">
        <p class="no-margins"><strong>Property Type:</strong> <?php echo isset($application['application_type']) && isset($this->config->item('mm8_application_type_names')[$application['application_type']]) ? $this->config->item('mm8_application_type_names')[$application['application_type']] : ''; ?></p>
        <p class="no-margins"><strong>Customer Type:</strong> <?php echo isset($application['application_offer_type']) && isset($this->config->item('mm8_offer_type_names')[$application['application_offer_type']]) ? $this->config->item('mm8_offer_type_names')[$application['application_offer_type']] : ''; ?></p>
        <hr class="hr-line-dashed m-t-sm m-b-sm">
        <p class="no-margins"><strong>Name:</strong>  <?php echo isset($application['full_name']) ? $application['full_name'] : ''; ?></p>
        <p class="no-margins"><strong>From:</strong> <?php echo isset($partner['portal_name']) ? $partner['portal_name'] : ''; ?></p>
        <p class="no-margins"><strong>Referred by:</strong> <?php echo isset($application['agent_referred_full_name']) ? $application['agent_referred_full_name'] : ''; ?></p>
        <p class="no-margins"><strong>Address:</strong> <?php
            $toAddress = isset($application['new_unit_number']) ? $application['new_unit_number'] . " " : "";
            $toAddress .= isset($application['new_street_number']) ? $application['new_street_number'] . " " : "";
            $toAddress .= isset($application['new_street_name']) ? $application['new_street_name'] . " " : "";
            $toAddress .= isset($application['new_street_type']) ? $application['new_street_type'] . " " : "";
            $toAddress .= isset($application['new_suburb']) ? $application['new_suburb'] . " " : "";
            $toAddress .= isset($application['new_state']) ? $application['new_state'] . " " : "";
            $toAddress .= isset($application['new_postcode']) ? $application['new_postcode'] : "";
            echo trim(preg_replace('/\s+/', ' ', $toAddress));
            ?></p>
        <p class="no-margins"><strong>Move Date:</strong> <?php echo isset($application['move_in_date']) ? $application['move_in_date'] : ''; ?></p>
        <hr class="hr-line-dashed m-t-sm m-b-sm">
        <p class="no-margins"><strong>Reference Code:</strong> <?php echo isset($application['reference_code']) ? '<a href="javascript:;" onclick="window.open(\'' . base_url() . 'connect/view/' . $partner['reference_code'] . '/' . $application['app_id'] . '' . '\',\'_blank\')">' . $application['reference_code'] . '</a>' : ''; ?></p>
    </div>
</div>
