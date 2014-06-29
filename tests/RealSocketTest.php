<?php namespace tests\Irediscent;

use Irediscent\Connection\SocketConnection;
use Irediscent;

class RealSocketTest extends RealAbstractTest
{
    function setUp()
    {
        $this->r = new Irediscent(new SocketConnection());
    }
}
