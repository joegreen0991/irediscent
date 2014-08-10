<?php namespace benchmark\Irediscent;

use Irediscent\Connection\SocketConnection;
use Irediscent\Connection\Serializer\IRedis;
use Irediscent;

include_once __DIR__ . '/AbstractBenchmark.php';

class IredisBenchmark extends AbstractBenchmark
{
    protected function getConnection($conn = null)
    {
        return new Irediscent(new SocketConnection($conn, new IRedis()));
    }
}