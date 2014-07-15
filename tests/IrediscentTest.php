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

    public function testItConnectsWithPasswordAndPassword()
    {
        $connection = $this->getMock('Irediscent\Connection\ConnectionInterface');

        // Configure the stub.
        $connection->expects($this->once())
                    ->method('connect');

        // Configure the stub.
        $connection->expects($this->at(1))
            ->method('write')
            ->with($this->equalTo(array('AUTH','password')));

        // Configure the stub.
        $connection->expects($this->at(2))
            ->method('write')
            ->with($this->equalTo(array('SELECT', 4)));


        new Irediscent($connection, 'password', array(
            'database' => 4
        ));
    }

    public function testItExecutesARawQuery()
    {
        $connection = $this->getMock('Irediscent\Connection\ConnectionInterface');

        // Configure the stub.
        $connection->expects($this->once())
            ->method('connect');

        // Configure the stub.
        $connection->expects($this->at(1))
            ->method('write')
            ->with($this->equalTo(array('GET','key', 1)));

        // Configure the stub.
        $connection->expects($this->at(2))
            ->method('write')
            ->with($this->equalTo(array('GET','key2', 2)));


        $db = new Irediscent($connection);

        $db->execute('GET', 'key', 1);

        $db->execute('GET', array('key2', 2));
    }

    public function testItExecutesAPipelinedCommand()
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

    public function testItCorrectlyUncorksNonFluentPipelineCommands()
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

        $obj = $redis->pipeline();

        $this->assertSame($redis, $obj);

        $redis->set('test', 1);
        $redis->get('test');
        $res = $redis->uncork();

        $this->assertEquals(array(
            true,
            1
        ), $res);
    }

    public function testItPerformsAMultiExecCommand()
    {
        list($redis, $connection) = $this->getObjectAndMock();

        $connection->expects($this->at(0))
            ->method('multiWrite')
            ->with($this->equalTo(array(
                array('MULTI'),
                array('SET','test', 1),
                array('GET','test'),
                array('EXEC')
            )))
            ->will($this->returnValue(array(
                true,
                1
            )));

        $res = $redis->multiExec(function($redis){
            $redis->set('test', 1);
            $redis->get('test');
        });

        $this->assertEquals(array(
            true,
            1
        ), $res);
    }

    public function testItDisconnects()
    {
        list($redis, $connection) = $this->getObjectAndMock();

        $connection->expects($this->at(0))
            ->method('connect');

        $connection->expects($this->at(1))
            ->method('disconnect');

        $redis->connect();

        $redis->disconnect();
    }

}
