<?php

use WithingsFetcher\Fetcher;

// Sleep data can be retrieved by "7-days ranges"
// Let's start from the oldest date and stack the calls until we arrive to today's date
return function($oauth, $userid, $from_date)
{
    $fetcher      = new Fetcher($oauth);
    $from_time    = strtotime($from_date . ' 12:00');
    $all_measures = [];
    $codes        = [
        0 => 'awake',
        1 => 'light',
        2 => 'deep',
        3 => 'rem',
    ];
    while($from_time < time())
    {
        $new_from_time = strtotime('+7 days', $from_time);
        echo 'From ' . date('Y-m-d H:i', $from_time) . "\n";
        $measures = $fetcher->getSleepMeasures(['startdate' => $from_time, 'enddate' => $new_from_time, 'userid' => $userid]);
        foreach($measures['body']['series'] as $measure)
        {
            $all_measures[] = [
                'start' => date('Y-m-d H:i', $measure['startdate']),
                'end'   => date('Y-m-d H:i', $measure['enddate']),
                'state' => $codes[$measure['state']],
            ];
        }
        $from_time = $new_from_time;
        sleep(1);
    }
    return $all_measures;
};
