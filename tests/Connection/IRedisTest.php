<?php namespace tests\Irediscent;

use Irediscent;

class IRedisTest extends \PHPUnit_Framework_TestCase
{

    public function testObject()
    {
        $obj = new Irediscent\Connection\IRedis();
    }

}
