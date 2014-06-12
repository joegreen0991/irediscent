<?php namespace IRedisent\Connection;

use Redis\Connection\IRedis;
use Redis\Connection\Redisent;

class Factory {

    protected $dsn;

    public function __construct($dsn = null)
    {
        $this->dsn = $dsn;
    }

    public function getInstance()
    {
        return static::make($this->dsn);
    }

    public static function make($dsn = null)
    {
        if(function_exists('phpiredis_connect'))
        {
            return new IRedis($dsn);
        }

        return new Redisent($dsn);
    }
}
