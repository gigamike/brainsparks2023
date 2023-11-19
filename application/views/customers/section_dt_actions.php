<?php

defined('BASEPATH') or exit('No direct script access allowed');

echo '<a class="btn btn-xs btn-primary btn-w-xs m-r-xs" href="' . base_url() . 'customers/view/' . $id . '">View</a>';
echo '<a class="btn btn-xs btn-primary btn-w-xs m-r-xs" href="' . base_url() . 'customers/update/' . $id . '">Edit</a>';
echo '<a class="btn btn-xs btn-danger btn-w-xs m-r-xs" href="javascript:;" onclick="delete_to(\'' . $id . '\')">Remove</a>';
