<?php

defined('BASEPATH') or exit('No direct script access allowed');

use Aws\Sns\SnsClient;
use Aws\Exception\AwsException;
use Aws\Credentials\Credentials;

class Backend_workflow_builder extends CI_Controller
{
    /*
     * NOTE: DONT USE THE FILE LOCKING AS THIS WILL
     * RUN EVERY MINUTE AS A DAEMON
     */

    protected $search_for;
    protected $_pageRows = 100;

    public function __construct()
    {
        parent::__construct();

        $this->load->model('workflow_builder_model');
        $this->load->model('workflow_builder_log_model');
        $this->load->model('customers_model');
        $this->load->model('communications_model');
        $this->load->model('workflow_builder_log_email_model');
        $this->load->model('workflow_builder_log_sms_model');

        $this->load->library('lock_library');
        $this->load->library('email_library');

        //sms library
        $library_name = '/Sms_library_' . $this->config->item('mm8_country_code');
        if (!file_exists(APPPATH . "libraries/" . $library_name . ".php")) {
            $library_name = '/Sms_library_AU';
        }
        $this->load->library($library_name, '', 'sms_library');

        $this->load->helper('ordinal');
    }

    public function index()
    {
        //make sure this is accessible only via cli
        if (!$this->input->is_cli_request()) {
            $this->load->view('errors/restricted_page');
            return;
        }
    }

    /*
     * DAEMON SCHEDULER GUIDE
     *
     *                      weekday    day     hour    minute  xminute
     *  weekly              1-7        -       0-23    0-59    -
     *  monthly             -          1-31    0-23    0-59    -
     *  daily               -          -       0-23    0-59    -
     *  hourly              -          -       -       0-59    -
     *  Every x Minute      -          -       -       -       1-59
     *
     *  weekday: day of the week (1 = Sunday, 2 = Monday, ...7 = Saturday)
     *  day: day of the month (1 to 31)
     *  hour: hour (0 to 23)
     *  minute: minute (0 to 59)
     *  xminute: x minute (1 to 59)
     *
     * /Applications/MAMP/bin/php/php7.4.26/bin/php /Users/michaelgerardgalon/Sites/hackathon/enstack2023.gigamike.net/public_html/index.php cli/backend_workflow_builder daemon
     *
     */

    public function daemon()
    {
        //make sure this is accessible only via cli
        if (!$this->input->is_cli_request()) {
            $this->load->view('errors/restricted_page');
            return;
        }

        date_default_timezone_set($this->config->item('mm8_db_timezone'));

        if (ENVIRONMENT == 'development') {
            // $command_head = 'exec /usr/bin/php ' . FCPATH . 'index.php cli/backend-workflow-builder ';
            $command_head = 'exec /Applications/MAMP/bin/php/php7.4.26/bin/php ' . FCPATH . 'index.php cli/backend-workflow-builder ';
        } else {
            $command_head = 'exec nohup /usr/bin/php ' . FCPATH . 'index.php cli/backend-workflow-builder ';
        }
        $command_tail = ' > /dev/null 2>&1 &';

        $filter = [
            'active' => 1,
        ];
        $order = [
            'date_added'
        ];
        $workflowBuilders = $this->workflow_builder_model->getCrons($filter, $order);
      
        if (count($workflowBuilders) > 0) {
            foreach ($workflowBuilders as $workflowBuilder) {
                if (ENVIRONMENT == 'development') {
                    echo $workflowBuilder->id
                    . "|" . $workflowBuilder->name
                    . "|" . $workflowBuilder->execute
                    . "\n";
                }

                switch ($workflowBuilder->execute) {
                    case 'every_day':
                        if (ENVIRONMENT == 'development') {
                            echo "\t Interval: " . str_pad($workflowBuilder->execute_time_hour, 2, "0", STR_PAD_LEFT)
                            . ":" . str_pad($workflowBuilder->execute_time_minute, 2, "0", STR_PAD_LEFT)
                            . "\n";

                            echo "\t Current Time: " . str_pad($workflowBuilder->current_hour, 2, "0", STR_PAD_LEFT)
                            . ":" . str_pad($workflowBuilder->current_minute, 2, "0", STR_PAD_LEFT)
                            . "\n";
                        }


                        if ($workflowBuilder->execute_time_hour == $workflowBuilder->current_hour && $workflowBuilder->execute_time_minute == $workflowBuilder->current_minute) {
                            $cmd = $command_head . "execute_action " . $workflowBuilder->id . $command_tail;
                            exec($cmd);

                            if (ENVIRONMENT == 'development') {
                                echo "\t\t Executed: " . $cmd;
                            }
                        }
                        break;
                    case 'every_week':
                        if (ENVIRONMENT == 'development') {
                            echo "\t Interval: " . $this->_getDayOfTheWeek($workflowBuilder->execute_weekday)
                            . " " . str_pad($workflowBuilder->execute_time_hour, 2, "0", STR_PAD_LEFT)
                            . ":" . str_pad($workflowBuilder->execute_time_minute, 2, "0", STR_PAD_LEFT)
                            . "\n";

                            echo "\t Current Day Time: " . $this->_getDayOfTheWeek($workflowBuilder->current_weekday)
                            . " " . str_pad($workflowBuilder->current_hour, 2, "0", STR_PAD_LEFT)
                            . ":" . str_pad($workflowBuilder->current_minute, 2, "0", STR_PAD_LEFT)
                            . "\n";
                        }

                        if ($workflowBuilder->execute_weekday == $workflowBuilder->current_weekday && $workflowBuilder->execute_time_hour == $workflowBuilder->current_hour && $workflowBuilder->execute_time_minute == $workflowBuilder->current_minute) {
                            $cmd = $command_head . "execute_action " . $workflowBuilder->id . $command_tail;
                            exec($cmd);

                            if (ENVIRONMENT == 'development') {
                                echo "\t\t Executed: " . $cmd;
                            }
                        }
                        break;
                    case 'every_month':
                        if (ENVIRONMENT == 'development') {
                            echo "\t Interval: " . ordinal($workflowBuilder->execute_day_of_month)
                            . " " . str_pad($workflowBuilder->execute_time_hour, 2, "0", STR_PAD_LEFT)
                            . ":" . str_pad($workflowBuilder->execute_time_minute, 2, "0", STR_PAD_LEFT)
                            . "\n";

                            echo "\t Current Month Time: " . ordinal($workflowBuilder->current_day)
                            . " " . str_pad($workflowBuilder->current_hour, 2, "0", STR_PAD_LEFT)
                            . ":" . str_pad($workflowBuilder->current_minute, 2, "0", STR_PAD_LEFT)
                            . "\n";
                        }

                        if ($workflowBuilder->execute_day_of_month == $workflowBuilder->current_day 
                            && $workflowBuilder->execute_time_hour == $workflowBuilder->current_hour 
                            && $workflowBuilder->execute_time_minute == $workflowBuilder->current_minute) {
                            $cmd = $command_head . "execute_action " . $workflowBuilder->id . $command_tail;
                            exec($cmd);

                            if (ENVIRONMENT == 'development') {
                                echo "\t\t Executed: " . $cmd;
                            }
                        }
                        break;
                    case 'every_5_minutes':
                        $timestamp1 = strtotime($workflowBuilder->date_last_run);
                        $timestamp2 = strtotime($this->database_tz_model->now());
                        $minutes = abs($timestamp2 - $timestamp1) / (60);

                        if (ENVIRONMENT == 'development') {
                            echo "\t Last Run: " . $workflowBuilder->date_last_run . "\n";
                            echo "\t Current Datetime: " . $this->database_tz_model->now() . "\n";
                            echo "\t Difference between two dates is " . $minutes . " minute(s)" . "\n";
                        }

                        if ($minutes >= 5) {
                            $cmd = $command_head . "execute_action " . $workflowBuilder->id . $command_tail;
                            exec($cmd);

                            if (ENVIRONMENT == 'development') {
                                echo "\t\t Executed: " . $cmd;
                            }
                        }
                        break;
                    case 'every_10_minutes':
                        $timestamp1 = strtotime($workflowBuilder->date_last_run);
                        $timestamp2 = strtotime($this->database_tz_model->now());
                        $minutes = abs($timestamp2 - $timestamp1) / (60);

                        if (ENVIRONMENT == 'development') {
                            echo "\t Last Run: " . $workflowBuilder->date_last_run . "\n";
                            echo "\t Current Datetime: " . $this->database_tz_model->now() . "\n";
                            echo "\t Difference between two dates is " . $minutes . " minute(s)" . "\n";
                        }

                        if ($minutes >= 10) {
                            $cmd = $command_head . "execute_action " . $workflowBuilder->id . $command_tail;
                            exec($cmd);

                            if (ENVIRONMENT == 'development') {
                                echo "\t\t Executed: " . $cmd;
                            }
                        }
                        break;
                    case 'every_15_minutes':
                        $timestamp1 = strtotime($workflowBuilder->date_last_run);
                        $timestamp2 = strtotime($this->database_tz_model->now());
                        $minutes = abs($timestamp2 - $timestamp1) / (60);

                        if (ENVIRONMENT == 'development') {
                            echo "\t Last Run: " . $workflowBuilder->date_last_run . "\n";
                            echo "\t Current Datetime: " . $this->database_tz_model->now() . "\n";
                            echo "\t Difference between two dates is " . $minutes . " minute(s)" . "\n";
                        }

                        if ($minutes >= 15) {
                            $cmd = $command_head . "execute_action " . $workflowBuilder->id . $command_tail;
                            exec($cmd);

                            if (ENVIRONMENT == 'development') {
                                echo "\t\t Executed: " . $cmd;
                            }
                        }
                        break;
                    case 'every_20_minutes':
                        $timestamp1 = strtotime($workflowBuilder->date_last_run);
                        $timestamp2 = strtotime($this->database_tz_model->now());
                        $minutes = abs($timestamp2 - $timestamp1) / (60);

                        if (ENVIRONMENT == 'development') {
                            echo "\t Last Run: " . $workflowBuilder->date_last_run . "\n";
                            echo "\t Current Datetime: " . $this->database_tz_model->now() . "\n";
                            echo "\t Difference between two dates is " . $minutes . " minute(s)" . "\n";
                        }

                        if ($minutes >= 20) {
                            $cmd = $command_head . "execute_action " . $workflowBuilder->id . $command_tail;
                            exec($cmd);

                            if (ENVIRONMENT == 'development') {
                                echo "\t\t Executed: " . $cmd;
                            }
                        }
                        break;
                    case 'every_25_minutes':
                        $timestamp1 = strtotime($workflowBuilder->date_last_run);
                        $timestamp2 = strtotime($this->database_tz_model->now());
                        $minutes = abs($timestamp2 - $timestamp1) / (60);

                        if (ENVIRONMENT == 'development') {
                            echo "\t Last Run: " . $workflowBuilder->date_last_run . "\n";
                            echo "\t Current Datetime: " . $this->database_tz_model->now() . "\n";
                            echo "\t Difference between two dates is " . $minutes . " minute(s)" . "\n";
                        }

                        if ($minutes >= 25) {
                            $cmd = $command_head . "execute_action " . $workflowBuilder->id . $command_tail;
                            exec($cmd);

                            if (ENVIRONMENT == 'development') {
                                echo "\t\t Executed: " . $cmd;
                            }
                        }
                        break;
                    case 'every_30_minutes':
                        $timestamp1 = strtotime($workflowBuilder->date_last_run);
                        $timestamp2 = strtotime($this->database_tz_model->now());
                        $minutes = abs($timestamp2 - $timestamp1) / (60);

                        if (ENVIRONMENT == 'development') {
                            echo "\t Last Run: " . $workflowBuilder->date_last_run . "\n";
                            echo "\t Current Datetime: " . $this->database_tz_model->now() . "\n";
                            echo "\t Difference between two dates is " . $minutes . " minute(s)" . "\n";
                        }

                        if ($minutes >= 30) {
                            $cmd = $command_head . "execute_action " . $workflowBuilder->id . $command_tail;
                            exec($cmd);

                            if (ENVIRONMENT == 'development') {
                                echo "\t\t Executed: " . $cmd;
                            }
                        }
                        break;
                    case 'every_1_hour':
                        $timestamp1 = strtotime($workflowBuilder->date_last_run);
                        $timestamp2 = strtotime($this->database_tz_model->now());
                        $hour = abs($timestamp2 - $timestamp1) / (60 * 60);

                        if (ENVIRONMENT == 'development') {
                            echo "\t Last Run: " . $workflowBuilder->date_last_run . "\n";
                            echo "\t Current Datetime: " . $this->database_tz_model->now() . "\n";
                            echo "\t Difference between two dates is " . $hour . " hour(s)" . "\n";
                        }

                        if ($hour >= 1) {
                            $cmd = $command_head . "execute_action " . $workflowBuilder->id . $command_tail;
                            exec($cmd);

                            if (ENVIRONMENT == 'development') {
                                echo "\t\t Executed: " . $cmd;
                            }
                        }

                        break;
                    case 'every_2_hours':
                        $timestamp1 = strtotime($workflowBuilder->date_last_run);
                        $timestamp2 = strtotime($this->database_tz_model->now());
                        $hour = abs($timestamp2 - $timestamp1) / (60 * 60);

                        if (ENVIRONMENT == 'development') {
                            echo "\t Last Run: " . $workflowBuilder->date_last_run . "\n";
                            echo "\t Current Datetime: " . $this->database_tz_model->now() . "\n";
                            echo "\t Difference between two dates is " . $hour . " hour(s)" . "\n";
                        }

                        if ($hour >= 2) {
                            $cmd = $command_head . "execute_action " . $workflowBuilder->id . $command_tail;
                            exec($cmd);

                            if (ENVIRONMENT == 'development') {
                                echo "\t\t Executed: " . $cmd;
                            }
                        }
                        break;
                    case 'every_3_hours':
                        $timestamp1 = strtotime($workflowBuilder->date_last_run);
                        $timestamp2 = strtotime($this->database_tz_model->now());
                        $hour = abs($timestamp2 - $timestamp1) / (60 * 60);

                        if (ENVIRONMENT == 'development') {
                            echo "\t Last Run: " . $workflowBuilder->date_last_run . "\n";
                            echo "\t Current Datetime: " . $this->database_tz_model->now() . "\n";
                            echo "\t Difference between two dates is " . $hour . " hour(s)" . "\n";
                        }

                        if ($hour >= 3) {
                            $cmd = $command_head . "execute_action " . $workflowBuilder->id . $command_tail;
                            exec($cmd);

                            if (ENVIRONMENT == 'development') {
                                echo "\t\t Executed: " . $cmd;
                            }
                        }
                        break;
                    case 'every_4_hours':
                        $timestamp1 = strtotime($workflowBuilder->date_last_run);
                        $timestamp2 = strtotime($this->database_tz_model->now());
                        $hour = abs($timestamp2 - $timestamp1) / (60 * 60);

                        if (ENVIRONMENT == 'development') {
                            echo "\t Last Run: " . $workflowBuilder->date_last_run . "\n";
                            echo "\t Current Datetime: " . $this->database_tz_model->now() . "\n";
                            echo "\t Difference between two dates is " . $hour . " hour(s)" . "\n";
                        }

                        if ($hour >= 4) {
                            $cmd = $command_head . "execute_action " . $workflowBuilder->id . $command_tail;
                            exec($cmd);

                            if (ENVIRONMENT == 'development') {
                                echo "\t\t Executed: " . $cmd;
                            }
                        }
                        break;
                    case 'every_5_hours':
                        $timestamp1 = strtotime($workflowBuilder->date_last_run);
                        $timestamp2 = strtotime($this->database_tz_model->now());
                        $hour = abs($timestamp2 - $timestamp1) / (60 * 60);

                        if (ENVIRONMENT == 'development') {
                            echo "\t Last Run: " . $workflowBuilder->date_last_run . "\n";
                            echo "\t Current Datetime: " . $this->database_tz_model->now() . "\n";
                            echo "\t Difference between two dates is " . $hour . " hour(s)" . "\n";
                        }

                        if ($hour >= 5) {
                            $cmd = $command_head . "execute_action " . $workflowBuilder->id . $command_tail;
                            exec($cmd);

                            if (ENVIRONMENT == 'development') {
                                echo "\t\t Executed: " . $cmd;
                            }
                        }
                        break;
                    case 'every_6_hours':
                        $timestamp1 = strtotime($workflowBuilder->date_last_run);
                        $timestamp2 = strtotime($this->database_tz_model->now());
                        $hour = abs($timestamp2 - $timestamp1) / (60 * 60);

                        if (ENVIRONMENT == 'development') {
                            echo "\t Last Run: " . $workflowBuilder->date_last_run . "\n";
                            echo "\t Current Datetime: " . $this->database_tz_model->now() . "\n";
                            echo "\t Difference between two dates is " . $hour . " hour(s)" . "\n";
                        }

                        if ($hour >= 6) {
                            $cmd = $command_head . "execute_action " . $workflowBuilder->id . $command_tail;
                            exec($cmd);

                            if (ENVIRONMENT == 'development') {
                                echo "\t\t Executed: " . $cmd;
                            }
                        }
                        break;
                    case 'every_7_hours':
                        $timestamp1 = strtotime($workflowBuilder->date_last_run);
                        $timestamp2 = strtotime($this->database_tz_model->now());
                        $hour = abs($timestamp2 - $timestamp1) / (60 * 60);

                        if (ENVIRONMENT == 'development') {
                            echo "\t Last Run: " . $workflowBuilder->date_last_run . "\n";
                            echo "\t Current Datetime: " . $this->database_tz_model->now() . "\n";
                            echo "\t Difference between two dates is " . $hour . " hour(s)" . "\n";
                        }

                        if ($hour >= 7) {
                            $cmd = $command_head . "execute_action " . $workflowBuilder->id . $command_tail;
                            exec($cmd);

                            if (ENVIRONMENT == 'development') {
                                echo "\t\t Executed: " . $cmd;
                            }
                        }
                        break;
                    case 'every_8_hours':
                        $timestamp1 = strtotime($workflowBuilder->date_last_run);
                        $timestamp2 = strtotime($this->database_tz_model->now());
                        $hour = abs($timestamp2 - $timestamp1) / (60 * 60);

                        if (ENVIRONMENT == 'development') {
                            echo "\t Last Run: " . $workflowBuilder->date_last_run . "\n";
                            echo "\t Current Datetime: " . $this->database_tz_model->now() . "\n";
                            echo "\t Difference between two dates is " . $hour . " hour(s)" . "\n";
                        }

                        if ($hour >= 8) {
                            $cmd = $command_head . "execute_action " . $workflowBuilder->id . $command_tail;
                            exec($cmd);

                            if (ENVIRONMENT == 'development') {
                                echo "\t\t Executed: " . $cmd;
                            }
                        }
                        break;
                    case 'every_9_hours':
                        $timestamp1 = strtotime($workflowBuilder->date_last_run);
                        $timestamp2 = strtotime($this->database_tz_model->now());
                        $hour = abs($timestamp2 - $timestamp1) / (60 * 60);

                        if (ENVIRONMENT == 'development') {
                            echo "\t Last Run: " . $workflowBuilder->date_last_run . "\n";
                            echo "\t Current Datetime: " . $this->database_tz_model->now() . "\n";
                            echo "\t Difference between two dates is " . $hour . " hour(s)" . "\n";
                        }

                        if ($hour >= 9) {
                            $cmd = $command_head . "execute_action " . $workflowBuilder->id . $command_tail;
                            exec($cmd);

                            if (ENVIRONMENT == 'development') {
                                echo "\t\t Executed: " . $cmd;
                            }
                        }
                        break;
                    case 'every_10_hours':
                        $timestamp1 = strtotime($workflowBuilder->date_last_run);
                        $timestamp2 = strtotime($this->database_tz_model->now());
                        $hour = abs($timestamp2 - $timestamp1) / (60 * 60);

                        if (ENVIRONMENT == 'development') {
                            echo "\t Last Run: " . $workflowBuilder->date_last_run . "\n";
                            echo "\t Current Datetime: " . $this->database_tz_model->now() . "\n";
                            echo "\t Difference between two dates is " . $hour . " hour(s)" . "\n";
                        }

                        if ($hour >= 10) {
                            $cmd = $command_head . "execute_action " . $workflowBuilder->id . $command_tail;
                            exec($cmd);

                            if (ENVIRONMENT == 'development') {
                                echo "\t\t Executed: " . $cmd;
                            }
                        }
                        break;
                    case 'every_11_hours':
                        $timestamp1 = strtotime($workflowBuilder->date_last_run);
                        $timestamp2 = strtotime($this->database_tz_model->now());
                        $hour = abs($timestamp2 - $timestamp1) / (60 * 60);

                        if (ENVIRONMENT == 'development') {
                            echo "\t Last Run: " . $workflowBuilder->date_last_run . "\n";
                            echo "\t Current Datetime: " . $this->database_tz_model->now() . "\n";
                            echo "\t Difference between two dates is " . $hour . " hour(s)" . "\n";
                        }

                        if ($hour >= 11) {
                            $cmd = $command_head . "execute_action " . $workflowBuilder->id . $command_tail;
                            exec($cmd);

                            if (ENVIRONMENT == 'development') {
                                echo "\t\t Executed: " . $cmd;
                            }
                        }
                        break;
                    case 'every_12_hours':
                        $timestamp1 = strtotime($workflowBuilder->date_last_run);
                        $timestamp2 = strtotime($this->database_tz_model->now());
                        $hour = abs($timestamp2 - $timestamp1) / (60 * 60);

                        if (ENVIRONMENT == 'development') {
                            echo "\t Last Run: " . $workflowBuilder->date_last_run . "\n";
                            echo "\t Current Datetime: " . $this->database_tz_model->now() . "\n";
                            echo "\t Difference between two dates is " . $hour . " hour(s)" . "\n";
                        }

                        if ($hour >= 12) {
                            $cmd = $command_head . "execute_action " . $workflowBuilder->id . $command_tail;
                            exec($cmd);

                            if (ENVIRONMENT == 'development') {
                                echo "\t\t Executed: " . $cmd;
                            }
                        }
                        break;
                    default:
                }
            }
        }
    }

    /*
     * THE FOLLOWING ARE THE MICRO TASKS THAT THE DAEMON WILL RUN
     *
     */


    /*
     *
     * Sample force run
     * /Applications/MAMP/bin/php/php7.4.26/bin/php /Users/michaelgerardgalon/Sites/hackathon/enstack2023.gigamike.net/public_html/index.php cli/backend-workflow-builder execute_action 1
     *
     */

    public function execute_action($workflow_builder_id = 0)
    {
        //lock
        if (!$this->lock_library->lock("hub_workflow_builder_" . $workflow_builder_id)) {
            echo "Another instance running";
            exit();
        }

        $workflowBuilder = $this->workflow_builder_model->getById($workflow_builder_id);
        if (!$workflowBuilder) {
            return false;
        }

        if (!$workflowBuilder->active) {
            return false;
        }

        if (trim($workflowBuilder->sql_statement) == '') {
            return false;
        }

        $customers = $this->workflow_builder_model->run_query($workflowBuilder->sql_statement);
        if ($customers === false) {
            return;
        }

        $this->db->trans_begin();

        if (count($customers) > 0) {
            foreach ($customers as $customer) {
                if ($workflowBuilder->execute_run == 'once') {
                    // check if action already executed

                    $filter = [
                        'workflow_builder_id' => $workflowBuilder->id,
                        'customer_id' => $customer['customer_id'],
                    ];
                    $workflowBuilderLogs = $this->workflow_builder_log_model->fetch($filter);
                    if (count($workflowBuilderLogs) > 0) {
                        continue;
                    }
                }

                if (ENVIRONMENT == 'development') {
                    echo "Application ID: " . $customer['customer_id'] . "\n";
                }

                $data = [
                    'workflow_builder_id' => $workflowBuilder->id,
                    'customer_id' => $customer['customer_id'],
                    'workflow_setup' => json_encode([
                        'json_dataset' => $workflowBuilder->json_dataset,
                        'sql_statement' => $workflowBuilder->sql_statement,
                        'action_email_send' => $workflowBuilder->action_email_send,
                        'action_email_subject' => $workflowBuilder->action_email_subject,
                        'action_email_html_body' => $workflowBuilder->action_email_html_body,
                        'action_email_with_system_template' => $workflowBuilder->action_email_with_system_template,
                        'action_sms_send' => $workflowBuilder->action_sms_send,
                        'action_sms_message' => $workflowBuilder->action_sms_message,
                        'execute' => $workflowBuilder->execute,
                        'execute_run' => $workflowBuilder->execute_run,
                        'execute_weekday' => $workflowBuilder->execute_weekday,
                        'execute_day_of_month' => $workflowBuilder->execute_day_of_month,
                        'execute_time_hour' => $workflowBuilder->execute_time_hour,
                        'execute_time_minute' => $workflowBuilder->execute_time_minute,
                    ]),
                ];
                $workflow_builder_log_id = $this->workflow_builder_log_model->save($data);
                if (!$workflow_builder_log_id) {
                    $message = "Backend Systems failed to insert Workflow Builder Log with Workflow Builder ID " . $workflowBuilder->id;
                    $this->email_library->notify_system_failure($message);
                    $this->db->trans_rollback();
                    exit(EXIT_SUCCESS);
                }

                $customerInfo = $this->customers_model->getById($customer['customer_id']);
                if ($customerInfo) {
                    // action send email
                    if ($workflowBuilder->action_email_send) {
                        if (ENVIRONMENT == 'development') {
                            echo "\t Action: Send Email\n";
                        }

                        $this->_customerEmail($customer, $customerInfo, $workflowBuilder, $workflow_builder_log_id);
                    }

                    // action send sms
                    if ($workflowBuilder->action_sms_send) {
                        $this->_customerSMS($customer, $customerInfo, $workflowBuilder, $workflow_builder_log_id);
                    }
                }
            }
        }

        $data = [
            'id' => $workflowBuilder->id,
            'date_last_run' => $this->database_tz_model->now(),
        ];
        $workflow_builder_id = $this->workflow_builder_model->save($data);
        if (!$workflow_builder_id) {
            $message = "Backend Systems failed to update Workflow Builder with Workflow Builder ID " . $workflowBuilder->id;
            $this->email_library->notify_system_failure($message);
            $this->db->trans_rollback();
            exit(EXIT_SUCCESS);
        }

        if ($this->db->trans_status() === false) {
            $message = "Backend Systems failed to insert Workflow Builder Log and update Workflow Builder with Workflow Builder ID " . $workflowBuilder->id;
            $this->email_library->notify_system_failure($message);
            $this->db->trans_rollback();
            exit(EXIT_SUCCESS);
        }

        $this->db->trans_commit();

        //dont forget to unlock when done
        $this->lock_library->release_lock();
    }

    private function _customerEmail($customer, $customerInfo, $workflowBuilder, $workflow_builder_log_id)
    {
        //queue email
        // better queue email rather than actual send to avoid bottleneck
        //replace tags in templates
        $search_for = [
            "[FULLNAME]",
            "[FIRSTNAME]",
            "[LASTNAME]",
            "[EMAIL]",
            "[MOBILEPHONE]",
        ];
        $replace_with = [
            $customerInfo->full_name,
            $customerInfo->first_name,
            $customerInfo->last_name,
            $customerInfo->email,
            $customerInfo->mobile_phone,
        ];

        $subject = str_replace($search_for, $replace_with, $workflowBuilder->action_email_subject);

        $html_template = str_replace($search_for, $replace_with, $workflowBuilder->action_email_html_body);

        $from = $this->config->item('mm8_development_email');
        $from_name = $this->config->item('mm8_development_email');
        $reply_to = $this->config->item('mm8_development_email');

        if (empty($customerInfo->date_unsubscribed)) {
            $to = $customerInfo->email;

            $email_dataset = $this->email_library->send_customer_email($customerInfo, $from_name, $from, $reply_to, $to, "", "", $subject, $html_template, "", true, false);

            $email_dataset['workflow_builder_log_id'] = $workflow_builder_log_id;

            if (isset($email_dataset['access_code'])) {
                unset($email_dataset['access_code']);
            }

            // /Applications/MAMP/bin/php/php7.4.26/bin/php /Users/michaelgerardgalon/Sites/hackathon/enstack2023.gigamike.net/public_html/index.php cli/backend_workflow_builder workflow_builder_email_gateway
            if (!$this->workflow_builder_log_email_model->save($email_dataset)) {
                $message = "Backend Systems failed to queue email with Workflow Builder ID " . $workflowBuilder->id;
                $this->email_library->notify_system_failure($message);
                $this->db->trans_rollback();
                exit(EXIT_SUCCESS);
            }
        }
    }

    private function _customerSMS($customer, $customerInfo, $workflowBuilder, $workflow_builder_log_id)
    {
        if (ENVIRONMENT == 'development') {
            echo "\t Action: Send SMS\n";
        }

        //replace tags in templates
        $search_for = [
            "[FULLNAME]",
            "[FIRSTNAME]",
            "[LASTNAME]",
            "[EMAIL]",
            "[MOBILEPHONE]",
        ];
        $replace_with = [
            $customerInfo->full_name,
            $customerInfo->first_name,
            $customerInfo->last_name,
            $customerInfo->email,
            $customerInfo->mobile_phone,
        ];

        $action_sms_message = str_replace($search_for, $replace_with, $workflowBuilder->action_sms_message);

        $sms_dataset = [
            'workflow_builder_log_id' => $workflow_builder_log_id,
            'customer_id' => $customerInfo->id,
            'from' => '+639086087306',
            'to' => $customerInfo->mobile_phone,
            'message' => $action_sms_message,
            'source' => 'aws',
         ];

         // /Applications/MAMP/bin/php/php7.4.26/bin/php /Users/michaelgerardgalon/Sites/hackathon/enstack2023.gigamike.net/public_html/index.php cli/backend_workflow_builder workflow_builder_sms_gateway
        if (!$this->workflow_builder_log_sms_model->save($sms_dataset)) {
            $message = "Backend Systems failed to queue SMS with Workflow Builder ID " . $workflowBuilder->id;
            $this->email_library->notify_system_failure($message);
            $this->db->trans_rollback();
            exit(EXIT_SUCCESS);
        }
    }

    private function _getDayOfTheWeek($weekDay)
    {
        $dayOfTheWeek = null;

        switch ($weekDay) {
            case 1:
                $dayOfTheWeek = 'Sunday';
                break;
            case 2:
                $dayOfTheWeek = 'Monday';
                break;
            case 3:
                $dayOfTheWeek = 'Tuesday';
                break;
            case 4:
                $dayOfTheWeek = 'Wednesday';
                break;
            case 5:
                $dayOfTheWeek = 'Thursday';
                break;
            case 6:
                $dayOfTheWeek = 'Friday';
                break;
            case 7:
                $dayOfTheWeek = 'Saturday';
                break;
        }

        return $dayOfTheWeek;
    }

    /*
     *
     * Interval: every minute
     * /Applications/MAMP/bin/php/php7.4.26/bin/php /Users/michaelgerardgalon/Sites/hackathon/enstack2023.gigamike.net/public_html/index.php cli/backend_workflow_builder workflow_builder_email_gateway
     * index.php cli/backend_workflow_builder workflow_builder_email_gateway
     *
     */

    public function workflow_builder_email_gateway()
    {
        ini_set('max_execution_time', 0);

        //make sure this is accessible only via cli
        if (!$this->input->is_cli_request()) {
            $this->load->view('errors/restricted_page');
            return;
        }

        //lock
        if (!$this->lock_library->lock('workflow_builder_email_gateway')) {
            echo "Another instance running";
            exit();
        }

        $this->_workflowBuilderEmailGateway();

        //dont forget to unlock when done
        $this->lock_library->release_lock();
    }

    private function _workflowBuilderEmailGateway()
    {
        if (ENVIRONMENT == 'development') {
            echo "Sending Emails...\n";
        }

        $filter = [
            'processed' => STATUS_NG,
            'is_paused' => STATUS_NG,
        ];

        $fields = [
            'id',
        ];
        $order = [
            'date_added',
        ];

        $logEmails = $this->workflow_builder_log_email_model->fetch($filter, $order, $this->_pageRows, null, $fields);
        if (count($logEmails) > 0) {
            foreach ($logEmails as $logEmail) {
                if (ENVIRONMENT == 'development') {
                    echo "\t" . $logEmail->id . "\n";
                }

                // echo $logEmail->id . "\n";
                //START
                $this->db->trans_begin();

                $logEmail = $this->workflow_builder_log_email_model->getById($logEmail->id);
                if ($logEmail) {

                    // send individual email
                    $email_data = [];
                    $email_data['from'] = $logEmail->from;
                    $email_data['from_name'] = $logEmail->from_name;
                    $email_data['reply_to'] = $logEmail->reply_to;
                    $email_data['to'] = $logEmail->to;
                    $email_data['subject'] = $logEmail->subject;
                    $email_data['html_message'] = $logEmail->html_message;
                    $email_data['text_message'] = $logEmail->text_message;
                    if ($logEmail->attachment !== null && $logEmail->attachment !== "") {
                        $email_data['attachment'] = explode("::", $logEmail->attachment);
                    }

                    // insert email tracker
                    $email_data['html_message'] = $this->_emailTracker($logEmail->id, $email_data['html_message']);

                    $status = $this->email_library->send($email_data);

                    $data = [
                        'id' => $logEmail->id,
                        'processed' => STATUS_OK,
                        'status' => $status,
                        'date_processed' => $this->database_tz_model->now(),
                    ];

                    $log_email_id = $this->workflow_builder_log_email_model->save($data);
                    if (!$log_email_id) {
                        $this->db->trans_rollback();
                        $message = "Backend Systems failed workflow builder email gateway.";
                        $this->email_library->notify_system_failure($message);
                        $this->lock_library->release_lock();
                        exit(EXIT_SUCCESS);
                    }
                }

                //COMMIT
                if ($this->db->trans_status() === false) {
                    $this->db->trans_rollback();
                    $message = "Backend Systems failed workflow builder email gateway.";
                    $this->email_library->notify_system_failure($message);
                    $this->lock_library->release_lock();
                    exit(EXIT_SUCCESS);
                }

                $this->db->trans_commit();
            }
        }
    }


    /*
     *
     * Interval: every minute
     * /Applications/MAMP/bin/php/php7.4.26/bin/php /Users/michaelgerardgalon/Sites/hackathon/enstack2023.gigamike.net/public_html/index.php cli/backend_workflow_builder workflow_builder_sms_gateway
     * index.php cli/backend_workflow_builder workflow_builder_sms_gateway
     *
     */

    public function workflow_builder_sms_gateway()
    {
        ini_set('max_execution_time', 0);

        //make sure this is accessible only via cli
        if (!$this->input->is_cli_request()) {
            $this->load->view('errors/restricted_page');
            return;
        }

        //lock
        if (!$this->lock_library->lock('workflow_builder_sms_gateway')) {
            echo "Another instance running";
            exit();
        }

        $this->_workflowBuilderSmsGateway();

        //dont forget to unlock when done
        $this->lock_library->release_lock();
    }

    private function _workflowBuilderSmsGateway()
    {
        if (ENVIRONMENT == 'development') {
            echo "Sending SMS...\n";
        }

        $filter = [
            'processed' => STATUS_NG,
            'is_paused' => STATUS_NG,
        ];

        $fields = [
            'id',
        ];
        $order = [
            'date_added',
        ];

        $logSmses = $this->workflow_builder_log_sms_model->fetch($filter, $order, $this->_pageRows, null, $fields);
        if (count($logSmses) > 0) {
            foreach ($logSmses as $logSms) {
                // if (ENVIRONMENT == 'development') {
                    echo "\t" . $logSms->id . "\n";
                // }

                //START
                $this->db->trans_begin();

                $logSms = $this->workflow_builder_log_sms_model->getById($logSms->id);
                if ($logSms) {
                    $credentials = new Credentials($this->config->item('mm8_aws_access_key_id'), $this->config->item('mm8_aws_secret_access_key'));

                    $snSclient = new SnsClient([
                        'region' => $this->config->item('mm8_aws_region'),
                        'version' => '2010-03-31',
                        'credentials' => $credentials,
                    ]);

                    try {
                        $result = $snSclient->publish([
                            'Message' => $logSms->message,
                            'PhoneNumber' => $logSms->to,
                        ]);
                        var_dump($result);
                    } catch (AwsException $e) {
                        // output error message if fails
                        error_log($e->getMessage());
                    }

                    $data = [
                        'id' => $logSms->id,
                        'processed' => STATUS_OK,
                        'status' => STATUS_OK,
                        'date_processed' => $this->database_tz_model->now(),
                    ];
                    $log_sms_id = $this->workflow_builder_log_sms_model->save($data);
                    if (!$log_sms_id) {
                        $this->db->trans_rollback();
                        $message = "Backend Systems failed workflow builder sms gateway.";
                        $this->email_library->notify_system_failure($message);
                        $this->lock_library->release_lock();
                        exit(EXIT_SUCCESS);
                    }
                }

                //COMMIT
                if ($this->db->trans_status() === false) {
                    $this->db->trans_rollback();
                    $message = "Backend Systems failed workflow builder sms gateway.";
                    $this->email_library->notify_system_failure($message);
                    $this->lock_library->release_lock();
                    exit(EXIT_SUCCESS);
                }

                $this->db->trans_commit();
            }
        }
    }

    private function _emailTracker($email_id, $html)
    {
        $emailTrackingImage = "<img width=\"0\" height=\"0\" src=\"" . base_url() . "email-tracker/workflow-builder?id=" . $this->encryption->url_encrypt($email_id) . "\">";
        $pos = strrpos($html, "</body>");
        if ($pos !== false) {
            $html = substr_replace($html, $emailTrackingImage, $pos, strlen("</body>"));
        } else {
            $html .= $emailTrackingImage;
        }

        return $html;
    }
}

//end of Backend_cron_daemon()
