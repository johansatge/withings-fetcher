<?php

namespace WithingsFetcher;

use WithingsFetcher\Error;

/**
 * This is a basic implementation of OAuth1 for Withings
 * The steps are the following:
 * - Generate a request token
 * - Generate an authentication URL (by using the request token)
 * - The user opens the URL and accepts the app: a callback URL is loaded, with his userid in the GET params
 * - Generate and store an access token (by using the request token)
 * - Query public resources (by using the access token)
 */
class OAuth
{

    // OAuth URLs
    private $requestTokenURL = 'https://oauth.withings.com/account/request_token';
    private $accessTokenURL  = 'https://oauth.withings.com/account/access_token';
    private $userAuthURL     = 'https://oauth.withings.com/account/authorize';

    private $apiKey    = null;
    private $apiSecret = null;

    private $oauthRequestToken       = null;
    private $oauthRequestTokenSecret = null;

    private $oauthAccessToken       = null;
    private $oauthAccessTokenSecret = null;

    /**
     * Build a new object
     * @param string $api_key
     * @param string $api_secret
     */
    public function __construct($api_key, $api_secret)
    {
        if (!is_string($api_key) || !is_string($api_secret))
        {
            throw new Error('API key and secret must be strings');
        }
        $this->apiKey         = $api_key;
        $this->apiSecret      = $api_secret;
    }

    /**
     * Get a public resource (by using the access token)
     * This method should be used by WithingsFetcher\Fetcher only
     * @param  string $url
     * @param  array  $params
     * @return array
     */
    public function getResource($url, $params = [])
    {
        $oauth_params = ['oauth_token' => $this->oauthAccessToken];
        $response     = $this->curl($this->getURL($url, array_merge($params, $oauth_params), $this->oauthAccessTokenSecret));
        try
        {
            $json = json_decode($response, true);
            return $json;
        }
        catch(Exception $error)
        {
            throw new Error('Could not decode JSON response');
        }
    }

    /**
     * Generate a request token and an authentication URL
     * The user has to open it in a browser, and accept the application
     * Request tokens are stored for later use, when generating the access tokens
     * @param  string $callback_url
     * @return array
     */
    public function getAuthenticationURL($callback_url)
    {
        if (!is_string($callback_url))
        {
            throw new Error('The callback URL must be a string');
        }
        $token = $this->extractTokenFromURL($this->getURL($this->requestTokenURL, ['oauth_callback' => $callback_url]));
        if ($token['error'])
        {
            throw new Error($token['error']);
        }
        $this->oauthRequestToken       = $token['oauth_token'];
        $this->oauthRequestTokenSecret = $token['oauth_token_secret'];
        return $this->getURL($this->userAuthURL, ['oauth_token' => $this->oauthRequestToken], $this->oauthRequestTokenSecret);
    }

    /**
     * Authenticate the user, by querying access tokens
     * This step must be called when the user has accepted the app
     * @return array
     */
    public function generateAccessToken()
    {
        if (empty($this->oauthRequestToken) || empty($this->oauthRequestTokenSecret))
        {
            throw new Error('This method can\'t be called before an authentication URL has been generated');
        }
        $token = $this->extractTokenFromURL($this->getURL($this->accessTokenURL, ['oauth_token' => $this->oauthRequestToken], $this->oauthRequestTokenSecret));
        if ($token['error'])
        {
            throw new Error($token['error']);
        }
        $this->setAccessToken($token['oauth_token'], $token['oauth_token_secret']);
        return [
            'oauth_token'        => $token['oauth_token'],
            'oauth_token_secret' => $token['oauth_token_secret'],
        ];
    }

    /**
     * Set the access token
     * It will be used when using the getResource method
     * @param string $oauth_token
     * @param string $oauth_token_secret
     */
    public function setAccessToken($oauth_token, $oauth_token_secret)
    {
        if (!is_string($oauth_token) || !is_string($oauth_token_secret))
        {
            throw new Error('Token and secret must be strings');
        }
        $this->oauthAccessToken       = $oauth_token;
        $this->oauthAccessTokenSecret = $oauth_token_secret;
    }

    /**
     * Extract a token and its secret from an URL
     * (When requesting an access/request token, the response body looks like this: oauth_token=xxx&oauth_token_secret=yyy)
     * @param  string $body
     * @return array
     */
    private function extractTokenFromURL($url)
    {
        $body = $this->curl($url);
        if (preg_match('#^oauth_token#', $body))
        {
            parse_str($body, $elements);
            $token = [
                'error' => false,
                'oauth_token'        => !empty($elements['oauth_token']) ? $elements['oauth_token'] : false,
                'oauth_token_secret' => !empty($elements['oauth_token_secret']) ? $elements['oauth_token_secret'] : false,
            ];
            return $token;
        }
        $json = json_decode($body, true);
        return [
            'error' => $json['message'],
        ];
    }

    /**
     * Generate a valid Oauth URL by signing it
     * More info about OAuth1 signature: https://oauth1.wp-api.org/docs/basics/Signing.html
     * @param  string $url
     * @param  array  $params       GET parameters
     * @param  string $token_secret The token secret depends on the OAuth step:
     *                              - When querying a request token, there is no token secret
     *                              - When querying an access token, we use the request token secret
     *                              - When querying a public resource, we use the access token secret 
     * @return string
     */
    private function getURL($url, $params = [], $token_secret = '')
    {
        // Merge the GET parameters with OAuth ones
        // Those parameters are required in EVERY OAuth URL
        // They also have to be sorted alphabetically
        $params = array_merge([
            'oauth_consumer_key'     => $this->apiKey,
            'oauth_nonce'            => md5(microtime()),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp'        => time(),
            'oauth_version'          => '1.0',
        ], $params);
        ksort($params);
        // Generate the OAuth signature (sha1-encoded with the api secret, and the token secret (if available))
        $oauth_signature_string    = 'GET&' . urlencode($url) . '&' . urlencode(http_build_query($params));
        $params['oauth_signature'] = base64_encode(hash_hmac('sha1', $oauth_signature_string, (urlencode($this->apiSecret) . '&' . urlencode($token_secret)), true));

        return $url . '?' . http_build_query($params);
    }

    /**
     * Perform a CURL request
     * @param  string $url
     * @return string
     */
    private function curl($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

}
