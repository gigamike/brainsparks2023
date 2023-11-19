<?php
/**
 * Extending Security calls to display custom message for csrf
 */
class MY_Security extends CI_Security
{
    public function __construct()
    {
        parent::__construct();
    }

    public function csrf_show_error()
    {
        $heading = "Action not allowed";
        $message = "The action you have requested is not allowed.";

        $_error = & load_class('Exceptions', 'core');
        echo $_error->show_error($heading, $message, 'csrf_error', 403);
        exit;
    }
}
