![Withings Fetcher](logo.png)

> Basic [Withings](http://oauth.withings.com/api/doc) fetch API written in PHP

---

* [About the project](#about-the-project)
* [Installation](#installation)
* [OAuth authentication](#oauth-authentication)
* [Fetching resources](#fetching-resources)
* [License](license.md)

## About the project

This tiny project has been made to help people make requests to the main Withings API endpoints: _Activity_, _Body_, _Sleep_ and _Workouts_.

It uses a [standalone OAuth1 implementation](src/WithingsFetcher/OAuth.php), that might interest you if you are trying to understand how to sign an API request, for instance (_that was the hard part for me_).

Also, [here are a few sample scripts](examples), based on that library.

## Installation

Download the [source code](https://github.com/johansatge/withings-fetcher/archive/master.zip) and install it wherever you want in your project.

Then, require the files:

```php
require 'path/to/the/library/src/autoload.php';

use WithingsFetcher\Error;
use WithingsFetcher\OAuth;
use WithingsFetcher\Fetcher;
```

_Please note, since this library is considered to be still in WIP, it is not published on [Composer](https://getcomposer.org/)._

## OAuth authentication

To be able to fetch data from the Withings API, we need a valid `access_token` (and the associated `secret`).

Here are the steps we need to follow in order to get one.

### Step 1. Get an authentication URL

```php
$oauth = new OAuth(YOUR_API_KEY, YOUR_API_SECRET);
try
{
    $auth_url = $oauth->getAuthenticationURL('http://your-oauth-callback-url');
}
catch(Error $error)
{
    var_dump($error->getMessage());
}
```

### Step 2. Validate the application

The user has to open the authentication URL in his browser, and _accept_ the app.

When it's done, he will be redirected to `http://your-oauth-callback-url?userid=1234567&oauth_token=...`.

* We will need his `userid` (that part is not covered by the API - you probably want to set the callback URL as an endpoint of your application, to extract the `userid` from the `$_GET` parameters)
* We don't need the other parameters contained in the callback URL

### Step 3. Get the access token

When the app has been validated (the user has 2mns), we can generate an access token:

```php
try
{
    $credentials = $oauth->generateAccessToken();
    var_dump($credentials);
    // array(3) {
    //     ["oauth_token"]=>
    //     string(60) "0c132bc6fc7bb253db02d2e424ef34018bb7fb30cb43bcbe1e1234512345"
    //     ["oauth_token_secret"]=>
    //     string(54) "d099592b25bcbde8b18240a657506c063f12345678901234567890"
    // }
}
catch(Error $error)
{
    var_dump($error->getMessage());
}
```

Once it has been issued, the access token should be stored (along with the user ID) and reused later.

### Step 4. Reuse the access token

If we have stored an `access_token` and want to reuse it, instead of following the whole authentication process again, we can do so:

```php
$oauth = new OAuth(YOUR_API_KEY, YOUR_API_SECRET);
$oauth->setAccessToken($stored_oauth_token, $stored_oauth_token_secret);
```

## Fetching resources

Now we have a working `$oauth` object, with a valid access token, we can query public resources by using the `Fetcher` class.

Instanciate a new fetcher:

```php
$fetcher = new Fetcher($oauth); // Please note the Fetcher needs our OAuth object
```

Then, the following methods are available:

```php
$activity_measures = $fetcher->getActivityMeasures($params);
```

```php
$body_measures = $fetcher->getBodyMeasures($params);
```

```php
$sleep_measures = $fetcher->getSleepMeasures($params);
```

```php
$sleep_summary = $fetcher->getSleepSummary($params);
```

```php
$workout_measures = $fetcher->getWorkouts($params);
```

`$params` is an array of Withings API parameters (like `startdate`, `enddate`...).

A real-world example:

```php
$measures = $fetcher->getActivityMeasures([
    'userid'       => '12345',
    'startdateymd' => '2015-12-01',
    'enddateymd'   => '2015-12-02',
]);
var_dump($measures);

// array(2) {
//   ["status"]=>
//   int(0)
//   ["body"]=>
//   array(1) {
//     ["activities"]=>
//     array(30) {
//       [0]=>
//       array(10) {
//         ["date"]=>
//         string(10) "2015-12-01"
//         ["steps"]=>
//         int(6)
//         ["distance"]=>
//         float(4.86)
//         ["calories"]=>
//         float(0.17)
//         ["totalcalories"]=>
//         float(1808.672)
//         ["elevation"]=>
//         int(0)
//         ["soft"]=>
//         int(0)
//         ["moderate"]=>
// ...
```

And that's it! Happy _self-quantifying_ :rocket: :mag:
