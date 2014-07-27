<?php namespace Irediscent\Connection;

use Irediscent\Connection\Serializer\IRedis;
use Irediscent\Connection\Serializer\PurePhp;

class Factory {

    protected $dsn;

    protected $timeout;

    /**
     * @param null $dsn
     * @param null $timeout Socket connection timeout
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
            $serializer = new IRedis();
        }
        else{
            $serializer = new PurePhp();
        }

        return new SocketConnection($serializer, $dsn, $timeout);
    }
}
