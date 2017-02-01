<?php

namespace WithingsFetcher;

class Error extends \Exception
{
    protected $message;
    protected $code;

    /**
     * Build a custom error
     * For now, uses the \Exception behavior
     * @param string $message
     * @param int    $code
     */
    public function __construct($message, $code = 0)
    {
        $this->message = is_string($message) ? $message : '';
        $this->code    = is_int($code) ? $code : 0;
    }
}
