<?php namespace tests\Irediscent;

use Irediscent\Connection\SocketConnection;
use Irediscent;

include_once 'RealAbstractTest.php';

class RealSocketTest extends RealAbstractTest
{
    function setUp()
    {
        $this->r = new Irediscent(new SocketConnection());
    }
}
