<?php

use WithingsFetcher\Fetcher;

// The activity endpoint is weird:
// There is no concept of pagination, each call returns max 100 items, and it's not always sorted by date
// Let's fetch the items by month, it seems to work well (at least the final number of measures is right in my case)
return function ($oauth, $userid, $from_date)
{
    $fetcher      = new Fetcher($oauth);
    $all_measures = [];
    while(strtotime($from_date) < time())
    {
        $new_from_date = date('Y-m-d', strtotime('+30 days', strtotime($from_date)));
        echo 'From ' . $from_date . "\n";
        $measures = $fetcher->getActivityMeasures(['startdateymd' => $from_date, 'enddateymd' => $new_from_date, 'userid' => $userid]);
        foreach($measures['body']['activities'] as $measure)
        {
            $all_measures[$measure['date']] = [
                'steps'    => $measure['steps'],
                'distance' => $measure['distance'],
                'calories' => $measure['calories'],
            ];
        }
        $from_date = $new_from_date;
        sleep(1);
    }
    return $all_measures;
};
