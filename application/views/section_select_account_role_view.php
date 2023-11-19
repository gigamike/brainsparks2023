<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<select id="accountSelectorRole" style="width:100%;" onchange="account_selector_role_changed()">
    <option value="" disabled selected>Select Role</option>
    <?php
    foreach ($this->session->utilihub_hub_user_access as $key => $val) {
        //skip current role
        if ($key == $this->session->utilihub_hub_user_role) {
            continue;
        }

        if (count($val) > 0) {
            $selected = $this->session->has_userdata('utilihub_hub_target_role') && $this->session->utilihub_hub_target_role == $key ? " selected" : "";
            echo "<option value=\"$key\"$selected>" . $this->config->item('mm8_agent_role_levels')[$key] . "</option>";
        }
    }
    ?>
</select>