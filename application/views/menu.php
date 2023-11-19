<?php

defined('BASEPATH') or exit('No direct script access allowed');

// if this menu items is being accessed through other roles (workspace admin / referring agent)
// then touchbase first, before going to manager page

echo '<li' . ($user_menu === "dashboard" ? ' class="active"' : '') . '><a href="' . base_url() . "dashboard" . '"><span class="nav-label">Dashboard</span></a></li>';

echo '<li' . ($user_menu === "assessment" ? ' class="active"' : '') . '><a href="' . base_url() . "assessment" . '"><span class="nav-label">Assessment</span></a></li>';

echo '<li' . ($user_menu === "consultations" ? ' class="active"' : '') . '><a href="' . base_url() . "consultations" . '"><span class="nav-label">Consultations</span></a></li>';

echo '<li' . ($user_menu === "therapy" ? ' class="active"' : '') . '><a href="' . base_url() . "therapy" . '"><span class="nav-label">Therapy</span></a></li>';


/*
echo '<li' . ($user_menu === "tickets" ? ' class="active"' : '') . '><a href="' . base_url() . "customers" . '"><span class="nav-label">Customers</span></a></li>';
/*
echo '<li' . ($user_menu === "merchants" ? ' class="active"' : '') . '><a href="' . base_url() . "products" . '"><span class="nav-label">Products</span></a></li>';

echo '<li' . ($user_menu === "workflow-builder" ? ' class="active"' : '') . '><a href="' . base_url() . "workflow-builder" . '"><span class="nav-label">Email/SMS Marketing Tool</span></a></li>';

echo '<li' . ($user_menu === "users" ? ' class="active"' : '') . '><a href="' . base_url() . "users" . '"><span class="nav-label">Users</span></a></li>';
*/

echo '<li' . ($user_menu === "settings" ? ' class="active"' : '') . '><a href="' . base_url() . "settings" . '"><span class="nav-label">Settings</span></a></li>';
