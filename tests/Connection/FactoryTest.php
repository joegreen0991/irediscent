<?php namespace tests\Irediscent;

use Irediscent;

class FactoryTest extends \PHPUnit_Framework_TestCase
{

    public function testObjectIsCreatedCorrectly()
    {
        $connectionFactory = new Irediscent\Connection\Factory();

        $instance = $connectionFactory->getInstance();

        $this->assertInstanceOf('Irediscent\Connection\ConnectionInterface', $instance);
    }

}
