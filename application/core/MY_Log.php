<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Log extends CI_Log {

	public function write_log($level, $msg)
	{

		$level = strtoupper($level);

        if(SENTRY_ENABLED){

            // map levels
            if($level == 'ERROR')
              \Sentry\configureScope(function (\Sentry\State\Scope $scope): void {
                $scope->setLevel(\Sentry\Severity::error());
              });
            elseif($level == 'DEBUG'){
                \Sentry\configureScope(function (\Sentry\State\Scope $scope): void {
                    $scope->setLevel(\Sentry\Severity::debug());
                });
            }
            elseif($level == 'INFO'){
                \Sentry\configureScope(function (\Sentry\State\Scope $scope): void {
                    $scope->setLevel(\Sentry\Severity::info());
                });
            }

            // only send errors to sentry
            if($level == 'ERROR'){
                \Sentry\captureException(new Exception($msg));
            }
            elseif($level == 'DEBUG' || $level == 'INFO'){
                \Sentry\addBreadcrumb(
                    new \Sentry\Breadcrumb(
                        Sentry\Breadcrumb::LEVEL_INFO,
                        Sentry\Breadcrumb::TYPE_DEFAULT,
                        'info', 
                        $msg
                    )
                );                
            }

        } // if enabled


        return parent::write_log($level, $msg);
	}
}
