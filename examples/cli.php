<?php

date_default_timezone_set('Europe/Paris');

require '../src/autoload.php';

use WithingsFetcher\OAuth;
use WithingsFetcher\Error;

/**
 * CLI options
 */

$options = [];
foreach($argv as $arg)
{
    preg_match_all('#^--([^=$]+)=?(.*)$#', $arg, $matches);
    if (!empty($matches[1][0]))
    {
        $options[$matches[1][0]] = !empty($matches[2][0]) ? $matches[2][0] : true;
    }
}

/**
 * CLI authentication
 */

$api         = json_decode(file_get_contents('.apikeys'), true);
$credentials = is_readable('.oauth') ? json_decode(file_get_contents('.oauth'), true) : false;

$oauth = new OAuth($api['api_key'], $api['api_secret']);

if (empty($credentials))
{
    try
    {
        $auth_url = $oauth->getAuthenticationURL('http://localhost/oauth');    
    }
    catch(Error $error)
    {
        echo $error->getMessage();
        exit(1);
    }
    echo 'Open the following URL, accept, and paste the user ID:' . "\n";
    echo str_repeat('-', 10) . "\n" . $auth_url . "\n" . str_repeat('-', 10) . "\n";
    $userid = trim(fgets(STDIN));

    try
    {
        $credentials = $oauth->generateAccessToken();
    }
    catch(Error $error)
    {
        echo $error->getMessage();
        exit(1);
    }
    $credentials['userid'] = $userid;
    file_put_contents('.oauth', json_encode($credentials));
}
else
{
    $oauth->setAccessToken($credentials['oauth_token'], $credentials['oauth_token_secret']);
}

/**
 * CLI tasks
 */

if (empty($options['since']) || !preg_match('#^[0-9]{4}-[0-9]{2}-[0-9]{2}$#', $options['since']))
{
    echo 'You have to specify a start date (--since=YYYY-MM-DD).' . "\n";
    exit(1);
}

if (!empty($options['sleep']))
{
    echo 'Getting sleep data' . "\n";
    $measures = (require 'fetch-sleep.php')($oauth, $credentials['userid'], $options['since']);
    file_put_contents('data/sleep.json', json_encode($measures, JSON_PRETTY_PRINT));
    echo count($measures) . ' measures saved.' . "\n";
}

if (!empty($options['activity']))
{
    echo 'Getting activity data' . "\n";
    $measures = (require 'fetch-activity.php')($oauth, $credentials['userid'], $options['since']);
    file_put_contents('data/activity.json', json_encode($measures, JSON_PRETTY_PRINT));
    echo count($measures) . ' measures saved.' . "\n";
}

if (!empty($options['body']))
{
    echo 'Getting body data' . "\n";
    $measures = (require 'fetch-body.php')($oauth, $credentials['userid'], $options['since']);
    file_put_contents('data/body.json', json_encode($measures, JSON_PRETTY_PRINT));
    echo count($measures) . ' measures saved.' . "\n";
}
