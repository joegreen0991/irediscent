<?php namespace Irediscent\Connection;

use Irediscent\Connection\Util\IRedisObject;
use Irediscent\Connection\Util\IRedisProxy;
use Irediscent\Exception\ConnectionException;

class IRedis extends ConnectionAbstract {

    /**
     * @var IRedisProxy
     */
    private $iredisProxy;

    public function __construct($dsn = null)
    {
        parent::__construct($dsn);

        $this->iredisProxy = new IRedisObject();
    }

    public function setIredisObject(IRedisObject $proxy)
    {
        $this->iredisProxy = $proxy;
    }

    public function connect()
    {
        $connection = $this->dsn->getMasterDsn();

        $this->redis = $this->iredisProxy->connect($connection['host'], $connection['port']);

        if ($this->redis === false)
        {
            throw new ConnectionException("Could not connect to {$connection['host']}:{$connection['port']}");
        }
    }

    public function disconnect()
    {
        $this->iredisProxy->disconnect($this->redis);

        $this->redis = null;
    }

    public function write($data)
    {
        $this->safeConnect();

        // `strval` map handles an issue affecting 'phpiredis_command_bs' where params must be strings.
        // This issue does not affect `phpiredis_multi_command_bs`
        return $this->iredisProxy->command($this->redis, array_map('strval', $data));
    }

    public function multiWrite($data)
    {
        $this->safeConnect();

        return $this->iredisProxy->multiCommand($this->redis, $data);
    }
}
