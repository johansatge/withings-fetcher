<?php

use WithingsFetcher\Fetcher;

// There are "limit" and "offset" parameters to (sort of) paginate the results
// But since we don't know the max limit (it's not documented), let's fetch the data in "30-days" range, it seems to work
return function($oauth, $userid, $from_date)
{
    $fetcher      = new Fetcher($oauth);
    $from_time    = strtotime($from_date . ' 12:00');
    $all_measures = [];
    $codes        = [
        1  => 'weight',
        4  => 'height',
        5  => 'fat_free_mass',
        6  => 'fat_ratio',
        8  => 'fat_mass_weight',
        9  => 'diastolic_blood_pressure',
        10 => 'systolic_blood_pressure',
        11 => 'heart_pulse',
        12 => 'temperature',
        54 => 'sp02',
        71 => 'body_temperature',
        73 => 'skin_temperature',
        76 => 'muscle_mass',
        77 => 'hydration',
        88 => 'bone_mass',
        91 => 'pulse_wave_velocity',
    ];
    while($from_time < time())
    {
        $new_from_time = strtotime('+30 days', $from_time);
        echo 'From ' . date('Y-m-d H:i', $from_time) . "\n";
        $measures = $fetcher->getBodyMeasures(['startdate' => $from_time, 'enddate' => $new_from_time, 'userid' => $userid]);
        foreach($measures['body']['measuregrps'] as $measure)
        {
            if ($measure['category'] != 1) // 1: real mesure
            {
                continue;
            }
            $date = date('Y-m-d H:i', $measure['date']);
            if (!isset($all_measures[$date]))
            {
                $all_measures[$date] = [];
            }
            foreach($measure['measures'] as $submeasure)
            {
                $value = $submeasure['value'] * pow(10, $submeasure['unit']);
                $all_measures[$date][$codes[$submeasure['type']]] = round($value, 3);
            }
        }
        $from_time = $new_from_time;
        sleep(1);
    }
    ksort($all_measures);
    return $all_measures;
};
