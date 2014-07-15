<?php namespace tests\Irediscent;

use Irediscent;

class IRedisTest extends \PHPUnit_Framework_TestCase
{

    public function testItCorrectlyPassesCommandsToIRedisObject()
    {
        $mock = $this->getMock('Irediscent\Connection\Util\IRedisObject');

        $mock->expects($this->once())
            ->method('connect')
            ->will($this->returnValue('#resource'));

        // Test a simple single write which fails to write the full set of data first time
        $mock->expects($this->at(1))
            ->method('command')
            ->with($this->equalTo('#resource'), $this->equalTo(array('DEL','key','data')))
            ->will($this->returnValue('OK')); // only writes 5 bytes



        // Try a multi write with one inline response and one bulk response
        $mock->expects($this->at(2))
            ->method('multiCommand')
            ->with($this->equalTo('#resource'), $this->equalTo(array(
                array('SET','key','data'),
                array('GET','key'),
            )))
            ->will($this->returnValue(array(
                1,
                'data'
            )));

        // Close the connection
        $mock->expects($this->once())
            ->method('disconnect')
            ->with($this->equalTo('#resource'));

        $obj = new Irediscent\Connection\IRedis();

        $obj->setIredisObject($mock);

        $this->assertFalse($obj->isConnected());

        $obj->connect();

        $this->assertTrue($obj->isConnected());

        $res = $obj->write(array(
            'DEL','key','data'
        ));

        $this->assertEquals('OK', $res);

        $resArray = $obj->multiWrite(array(
            array('SET','key','data'),
            array('GET','key')
        ));

        $this->assertEquals(array(
            1, 'data'
        ), $resArray);

        $obj->disconnect();

        $this->assertFalse($obj->isConnected());

    }

    public function testItReConnectsWhenAsked()
    {
        $mock = $this->getMock('Irediscent\Connection\Util\IRedisObject');

        $mock->expects($this->at(0))
            ->method('connect')
            ->will($this->returnValue('#resource'));

        // Close the connection
        $mock->expects($this->at(1))
            ->method('disconnect')
            ->with($this->equalTo('#resource'));


        $mock->expects($this->at(2))
            ->method('connect')
            ->will($this->returnValue('#resource'));

        $obj = new Irediscent\Connection\IRedis();

        $obj->setIredisObject($mock);

        $this->assertFalse($obj->isConnected());

        $obj->connect();

        $this->assertTrue($obj->isConnected());

        $obj->reconnect();

        $this->assertTrue($obj->isConnected());
    }


    public function testItAutoConnectsWhenFirstCommandIsPerformed()
    {
        $mock = $this->getMock('Irediscent\Connection\Util\IRedisObject');

        $mock->expects($this->at(0))
            ->method('connect')
            ->will($this->returnValue('#resource'));

        $mock->expects($this->once())
            ->method('command');

        $obj = new Irediscent\Connection\IRedis();

        $obj->setIredisObject($mock);

        $this->assertFalse($obj->isConnected());

        $obj->write(array(
            'data'
        ));

        $this->assertTrue($obj->isConnected());
    }

    /**
     * @expectedException \Irediscent\Exception\RedisException
     * @expectedMessage This is an error
     */
    /*
    public function testItThrowsRedisExceptionOnServerError()
    {
        $mock = $this->getMock('Irediscent\Connection\SocketObject');

        $mock->expects($this->once())
            ->method('write')
            ->will($this->returnValue(14));

        $mock->expects($this->once())
            ->method('gets')
            ->will($this->returnValue('-This is an error'));

        $obj = new Irediscent\Connection\SocketConnection();

        $obj->setSocketObject($mock);

        $obj->write(array(
            'data'
        ));
    }
*/
    /**
     * @expectedException \Irediscent\Exception\ConnectionException
     */

    public function testItThrowsConnectionExceptionWhenServerConnectionOpenFails()
    {
        $mock = $this->getMock('Irediscent\Connection\Util\IRedisObject');

        $mock->expects($this->once())
            ->method('connect')
            ->will($this->returnValue(false));


        $obj = new Irediscent\Connection\IRedis();

        $obj->setIredisObject($mock);

        $obj->connect();
    }
}
