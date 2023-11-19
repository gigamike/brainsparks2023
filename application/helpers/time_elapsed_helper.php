<?php if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

if (! function_exists('time_elapsed')) {
    function time_elapsed($datetime, $full = false)
    {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = [
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        ];
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) {
            $string = array_slice($string, 0, 1);
        }
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }
}

if (! function_exists('time_elapsed_time_diff')) {
    function time_elapsed_datetime_diff($from, $to, $full = false, $with_text = true)
    {
        $from = new DateTime($from);
        $to = new DateTime($to);
        $diff = $to->diff($from);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = [
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        ];
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) {
            $string = array_slice($string, 0, 1);
        }

        if ($with_text) {
            return $string ? implode(', ', $string) . ' ago' : 'just now';
        } else {
            return implode(', ', $string);
        }
    }
}

if (! function_exists('get_hours_difference')) {
    function get_hours_difference($start, $end)
    {
        $time1 = new DateTime($start);
        $time2 = new DateTime($end);
        $time_diff = $time1->diff($time2);
        return $time_diff->h;
    }
}

if (! function_exists('get_timestamp_in_ms')) {
    function get_timestamp_in_ms($input, $tz = null){
        $output = '';
        $old_tz = date_default_timezone_get();

        if(!is_null($tz))
            date_default_timezone_set($tz);

        $t = new DateTime($input);
        $output = $t->getTimestamp() * 1000;
        
        if(!is_null($tz))
            date_default_timezone_set($old_tz);

        return $output;
    }
}

if (! function_exists('is_between')) {
    function is_between($from, $till, $input)
    {
        $f = DateTime::createFromFormat('!H:i:s', $from);
        $t = DateTime::createFromFormat('!H:i:s', $till);
        $i = DateTime::createFromFormat('!H:i:s', $input);
        if ($f > $t) {
            $t->modify('+1 day');
        }
        return ($f <= $i && $i <= $t) || ($f <= $i->modify('+1 day') && $i <= $t);
    }
}

if (! function_exists('get_minutes_difference')) {
    function get_minutes_difference($start, $end)
    {
        $time1 = new DateTime($start);
        $time2 = new DateTime($end);
        $time_diff = $time1->diff($time2);
        return $time_diff->i;
    }
}
