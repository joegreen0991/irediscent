<?php namespace Irediscent\Connection;

class IRedis extends ConnectionAbstract {

    public function connect()
    {
        $connection = $this->dsn->getMasterDsn();

        $this->redis = phpiredis_connect($connection['host'], $connection['port']);

        if ($this->redis === false)
        {
            throw new ConnectionException("Could not connect to {$connection['host']}:{$connection['port']}");
        }
    }

    public function disconnect()
    {
        phpiredis_disconnect($this->redis);
    }

    public function write($data)
    {
        $this->safeConnect();

        // Handle issue with 'phpiredis_command_bs' where params must be strings
        // Doesnt affect 'phpiredis_multi_command_bs'
        return phpiredis_command_bs($this->redis, array_map('strval', $data));
    }

    public function multiWrite($data)
    {
        $this->safeConnect();

        return phpiredis_multi_command_bs($this->redis, $data);
    }
}
