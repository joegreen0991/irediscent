<?php namespace Irediscent\Connection;

class IRedis extends ConnectionAbstract {

    public function connect()
    {
        $this->redis = phpiredis_connect($this->host, $this->port);
    }

    public function disconnect()
    {
        phpiredis_disconnect($this->redis);
    }

    public function write($data)
    {
        // Handle issue with 'phpiredis_command_bs' where params must be strings
        // Doesnt affect 'phpiredis_multi_command_bs'
        return phpiredis_command_bs($this->redis, array_map('strval', $data));
    }

    public function multiWrite($data)
    {
        return phpiredis_multi_command_bs($this->redis, $data);
    }
}
