<?php

namespace WithingsFetcher;

class Fetcher
{

    private $oauth = null;

    /**
     * Fetcher constructor
     * If the latency option is enabled, wait at least 1s between each API call
     * (The Withings API has a 60 calls per minute limit on all endpoints)
     * @param  object  $oauth
     */
    public function __construct($oauth)
    {
        if (!is_a($oauth, 'WithingsFetcher\OAuth'))
        {
            trigger_error('$oauth must be a WithingsFetcher\OAuth instance', E_USER_ERROR);
        }
        $this->oauth = $oauth;
    }

    /**
     * Activity measures
     * @param  array $params
     * @return array
     */
    public function getActivityMeasures($params = [])
    {
        $params = array_merge($params, ['action' => 'getactivity']);
        return $this->oauth->getResource('https://wbsapi.withings.net/v2/measure', $params);
    }

    /**
     * Body measures
     * @param  array $params
     * @return array
     */
    public function getBodyMeasures($params = [])
    {
        $params = array_merge($params, ['action' => 'getmeas']);
        return $this->oauth->getResource('https://wbsapi.withings.net/measure', $params);
    }

    /**
     * Sleep measures
     * @param  array $params
     * @return array
     */
    public function getSleepMeasures($params = [])
    {
        $params = array_merge($params, ['action' => 'get']);
        return $this->oauth->getResource('https://wbsapi.withings.net/v2/sleep', $params);
    }

    /**
     * Sleep summary
     * @param  array $params
     * @return array
     */
    public function getSleepSummary($params = [])
    {
        $params = array_merge($params, ['action' => 'getsummary']);
        return $this->oauth->getResource('https://wbsapi.withings.net/v2/sleep', $params);
    }

    /**
     * Workouts
     * @param  array $params
     * @return array
     */
    public function getWorkouts($params = [])
    {
        $params = array_merge($params, ['action' => 'getworkouts']);
        return $this->oauth->getResource('https://wbsapi.withings.net/v2/measure', $params);
    }

}
