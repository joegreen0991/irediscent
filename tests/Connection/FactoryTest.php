<?php namespace tests\Irediscent;

use Irediscent;

class FactoryTest extends \PHPUnit_Framework_TestCase
{

    public function testObjectIsCreatedCorrectly()
    {
        $instance = Irediscent\Connection\Factory::make();

        $this->assertInstanceOf('Irediscent\Connection\ConnectionInterface', $instance);
    }

}
