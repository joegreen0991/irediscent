<?php namespace Irediscent\Connection;

class Factory {

    protected $dsn;

    protected $timeout;

    /**
     * @param null $dsn
     * @param null $timeout Timeout is for streamconnection only
     */
    public function __construct($dsn = null, $timeout = null)
    {
        $this->dsn = $dsn;

        $this->timeout = $timeout;
    }

    public function getInstance()
    {
        return static::make($this->dsn, $this->timeout);
    }

    /**
     * @param null $dsn
     * @param null $timeout timeout is only for stream connection
     * @return IRedis|Redisent
     */
    public static function make($dsn = null, $timeout = null)
    {
        if(function_exists('phpiredis_connect'))
        {
            return new IRedis($dsn);
        }

        return new SocketConnection($dsn, $timeout);
    }
}
