<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Generator_library
{
    public function __construct()
    {
        $this->CI = & get_instance();
    }

    /*
    *
    * generates a random password of length minimum 8
    * contains at least one lower case letter, one upper case letter, one number and one special character, not including ambiguous characters like iIl|1 0oO
    *
     */
    public function randomPassword($len = 8, $is_set_special_chars = false)
    {

        //enforce min length 8
        if ($len < 8) {
            $len = 8;
        }

        //define character libraries - remove ambiguous characters like iIl|1 0oO
        $sets = [];
        $sets[] = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $sets[] = 'abcdefghjkmnpqrstuvwxyz';
        $sets[] = '123456789';
        if ($is_set_special_chars) {
            $sets[]  = '~!@#$%^&*(){}[],./?';
        }
        
        $password = '';
    
        //append a character from each set - gets first 4 characters
        foreach ($sets as $set) {
            $password .= $set[array_rand(str_split($set))];
        }

        //use all characters to fill up to $len
        while (strlen($password) < $len) {
            //get a random set
            $randomSet = $sets[array_rand($sets)];
        
            //add a random char from the random set
            $password .= $randomSet[array_rand(str_split($randomSet))];
        }
    
        //shuffle the password string before returning!
        return str_shuffle($password);
    }

    public function usernameBasedFromName($firstName, $lastName, $number = null)
    {
        $firstName = str_replace(' ', '', $firstName);
        $lastName = str_replace(' ', '', $lastName);

        $firstName = strtolower(trim($firstName));
        $lastName = strtolower(trim($lastName));

        if ($number) {
            return $firstName . $lastName . rand(0, $number);
        } else {
            return $firstName . $lastName;
        }
    }
}
