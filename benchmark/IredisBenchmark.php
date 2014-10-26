<?php namespace benchmark\Irediscent;

use Irediscent\Connection\IredisSocketConnection;
use Irediscent;

include_once __DIR__ . '/AbstractBenchmark.php';

class IredisBenchmark extends AbstractBenchmark
{
    protected function getConnection($conn = null)
    {
        return new Irediscent(new IredisSocketConnection($conn));
    }
}
