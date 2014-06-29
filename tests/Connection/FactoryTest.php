<?php namespace tests\Irediscent;

use Irediscent;

class FactoryTest extends \PHPUnit_Framework_TestCase
{

    public function testObject()
    {
        $obj = new Irediscent\Connection\Factory();

        $instance = $obj->getInstance();

        $this->assertInstanceOf('Irediscent\Connection\ConnectionInterface', $instance);
    }

}
