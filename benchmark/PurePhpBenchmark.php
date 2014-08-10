<?php namespace benchmark\Irediscent;

use Irediscent\Connection\SocketConnection;
use Irediscent\Connection\Serializer\PurePhp;
use Irediscent;

include_once __DIR__ . '/AbstractBenchmark.php';

class PurePhpBenchmark extends AbstractBenchmark
{
    protected function getConnection($conn = null)
    {
        return new Irediscent(new SocketConnection($conn, new PurePhp()));
    }
}