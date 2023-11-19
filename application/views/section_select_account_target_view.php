<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<select id="accountSelectorTarget" style="width:100%" onchange="account_selector_target_changed()">
    <option value="" disabled selected>Select Account</option>
    <?php
    if (!isset($new_role) || empty($new_role)) {
        $new_role = $this->session->utilihub_hub_target_role;
        $new_id = $this->session->utilihub_hub_target_id;
    }

    //skip current role
    if ($new_role != $this->session->utilihub_hub_user_role) {
        foreach ($this->session->utilihub_hub_user_access[$new_role] as $key => $val) {
            $selected = isset($new_id) && $new_id == $key ? " selected" : "";
            echo "<option value=\"" . $key . "\"" . $selected . ">" . $val . "</option>";
        }
    }
    ?>
</select>