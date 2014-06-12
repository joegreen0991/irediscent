<?php namespace tests\Irediscent;

use Irediscent;

class IrediscentTest extends \PHPUnit_Framework_TestCase
{

    public function getObjectAndMock()
    {
        $connection = $this->getMock('Irediscent\Connection\ConnectionInterface');

        // Configure the stub.
        $connection->expects($this->any())
                   ->method('connect');

        return array(new Irediscent($connection), $connection);
    }

    public function testObjectConnectsWithPassword()
    {
        $connection = $this->getMock('Irediscent\Connection\ConnectionInterface');

        // Configure the stub.
        $connection->expects($this->once())
                    ->method('connect');

        // Configure the stub.
        $connection->expects($this->once())
                   ->method('write', array('auth','password'));

        new Irediscent($connection, 'password');
    }

    public function testPipeline()
    {
        list($redis, $connection) = $this->getObjectAndMock();

        $connection->expects($this->once())
                    ->method('multiWrite')
                    ->with($this->equalTo(array(
                        array('SET','test', 1),
                        array('GET','test')
                    )))
                    ->will($this->returnValue(array(
                        true,
                        1
                    )));

        $res = $redis->pipeline(function($redis){
            $redis->set('test', 1);
            $redis->get('test');
        });

        $this->assertEquals(array(
            true,
            1
        ), $res);
    }

}
