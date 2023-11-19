<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('ordinal'))
{
  function ordinal($input_number) {
      $number = (string) $input_number;
      $last_digit = substr($number, -1);
      $second_last_digit = substr($number, -2, 1);
      $suffix = 'th';
      if ($second_last_digit != '1'){
        switch ($last_digit){
          case '1':
            $suffix = 'st';
            break;
          case '2':
            $suffix = 'nd';
            break;
          case '3':
            $suffix = 'rd';
            break;
          default:
            break;
        }
      }
      if ((string) $number === '1') $suffix = 'st';
      return $number.$suffix;
  }
}
