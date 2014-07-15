<?php namespace Irediscent\Connection\Util;

/**
 * @codeCoverageIgnore
 */
class IRedisObject {

    public function connect($host, $port)
    {
        return phpiredis_connect($host, $port);
    }

    public function disconnect($handle)
    {
        return phpiredis_disconnect($handle);
    }

    public function command($handle, $data)
    {
        return phpiredis_command_bs($handle, $data);
    }

    public function multiCommand($handle, $data)
    {
        return phpiredis_multi_command_bs($handle, $data);
    }
}
