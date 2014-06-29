<?php namespace tests\Irediscent;

use Irediscent\Connection\IRedis;
use Irediscent;

class RealIredisTest extends RealAbstractTest
{

    function setUp()
    {
        $this->r = new Irediscent(new IRedis());
    }
}
