<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Lock_library
{
    protected $lock_dir = FCPATH . "locks/";
    protected $fp;

    public function __construct()
    {
        $this->CI = & get_instance();
        $this->CI->load->library('email_library');
        if (!file_exists($this->lock_dir)) {
            $oldumask = umask(0);
            mkdir($this->lock_dir, 0775, true);
            umask($oldumask);

            if (!file_exists($this->lock_dir)) {
                $this->CI->email_library->notify_system_failure("Backend Systems failed to create the directory " . $this->lock_dir);
                exit(EXIT_ERROR);
            }
        }
    }

    public function lock($lockfile_name)
    {
        $this->fp = fopen($this->lock_dir . $lockfile_name . ".lock", "w");

        if (!flock($this->fp, LOCK_EX | LOCK_NB)) {
            return false;
        }
        return true;
    }

    public function release_lock()
    {
        flock($this->fp, LOCK_UN);
        fclose($this->fp);
    }
}
