<?php namespace IRedisent\Connection;

abstract class ConnectionAbstract implements ConnectionInterface {

    /**
     * Socket connection to the Redis server
     * @var resource
     * @access private
     */
    protected $redis;

    /**
     * Creates a Redisent connection to the Redis server at the address specified by {@link $dsn}.
     * The default connection is to the server running on localhost on port 6379.
     * @param string $dsn The data source name of the Redis server
     * @param float $timeout The connection timeout in seconds
     */
    public function __construct($dsn = null) {
        $dsn = parse_url($dsn);
        $this->host = isset($this->dsn['host']) ? $dsn['host'] : 'localhost';
        $this->port = isset($this->dsn['port']) ? $dsn['port'] : 6379;

        $this->connect();
    }
}
