<?php namespace tests\Irediscent;

use Irediscent\Connection\SocketConnection;
use Irediscent;

include_once 'RealAbstractTest.php';

class RealSocketTest extends RealAbstractTest
{
    protected function getConnection($conn = null)
    {
        return new Irediscent(new SocketConnection($conn));
    }
}
