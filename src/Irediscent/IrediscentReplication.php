<?php namespace Irediscent;

use Irediscent\Connection\ConnectionInterface;

class IrediscentReplication extends \Irediscent {

    /**
     * @var array of \Irediscent\Connection\ConnectionInterfaces;
     */
    protected $slaveConnections = array();

    protected static $readOnlyCommands = array(
            'EXISTS'            => true,
            'TYPE'              => true,
            'KEYS'              => true,
            'SCAN'              => true,
            'RANDOMKEY'         => true,
            'TTL'               => true,
            'GET'               => true,
            'MGET'              => true,
            'SUBSTR'            => true,
            'STRLEN'            => true,
            'GETRANGE'          => true,
            'GETBIT'            => true,
            'LLEN'              => true,
            'LRANGE'            => true,
            'LINDEX'            => true,
            'SCARD'             => true,
            'SISMEMBER'         => true,
            'SINTER'            => true,
            'SUNION'            => true,
            'SDIFF'             => true,
            'SMEMBERS'          => true,
            'SSCAN'             => true,
            'SRANDMEMBER'       => true,
            'ZRANGE'            => true,
            'ZREVRANGE'         => true,
            'ZRANGEBYSCORE'     => true,
            'ZREVRANGEBYSCORE'  => true,
            'ZCARD'             => true,
            'ZSCORE'            => true,
            'ZCOUNT'            => true,
            'ZRANK'             => true,
            'ZREVRANK'          => true,
            'ZSCAN'             => true,
            'ZLEXCOUNT'         => true,
            'ZRANGEBYLEX'       => true,
            'HGET'              => true,
            'HMGET'             => true,
            'HEXISTS'           => true,
            'HLEN'              => true,
            'HKEYS'             => true,
            'HVALS'             => true,
            'HGETALL'           => true,
            'HSCAN'             => true,
            'PING'              => true,
            'AUTH'              => true,
            'SELECT'            => true,
            'ECHO'              => true,
            'QUIT'              => true,
            'OBJECT'            => true,
            'BITCOUNT'          => true,
            'TIME'              => true,
            'PFCOUNT'           => true,
        );

    /**
     * @param string|ConnectionInterface $connection The data source name of the Redis server
     * @param string $password
     * @param array $options
     */
    public function addSlave($slave)
    {
        $connection = $this->resolveConnection($slave);

        $this->slaveConnections[] = $connection;
    }

    protected function pickRandomSlave()
    {
        return array_rand($this->slaveConnections);
    }

    protected function isReadonlyCommand($command)
    {
        return isset(self::$readOnlyCommands[strtolower($command)]);
    }

}
