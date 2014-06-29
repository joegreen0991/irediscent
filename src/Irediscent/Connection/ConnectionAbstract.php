<?php namespace Irediscent\Connection;

use Irediscent\DsnProvider\DsnProviderInterface;
use Irediscent\DsnProvider\StaticProvider;

abstract class ConnectionAbstract implements ConnectionInterface {

    /**
     * Socket connection to the Redis server
     * @var resource
     * @access private
     */
    protected $redis;

    /**
     * @var \Irediscent\DsnProvider\DsnProviderInterface
     */
    protected $dsn;

    public function __construct($dsn = null)
    {
        $this->dsn = $dsn instanceof DsnProviderInterface ? $dsn : new StaticProvider($dsn);
    }

    /**
     * @return bool
     */
    public function isConnected()
    {
        return $this->redis !== null;
    }

    /*
     *
     */
    protected function safeConnect()
    {
        if(!$this->isConnected())
        {
            $this->connect();
        }
    }

    /**
     *
     */
    public function reconnect()
    {
        if($this->isConnected())
        {
            $this->disconnect();
        }

        $this->connect();
    }
}
