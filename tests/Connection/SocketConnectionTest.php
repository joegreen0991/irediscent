<?php namespace tests\Irediscent;

use Irediscent;

class SocketConnectionTest extends \PHPUnit_Framework_TestCase
{

    public function testObject()
    {
        $mock = $this->getMock('Irediscent\Connection\SocketObject');

        $mock->expects($this->once())
             ->method('open')
             ->will($this->returnValue('#resource'));

        // Test a simple single write which fails to write the full set of data first time
        $mock->expects($this->at(1))
            ->method('write')
            ->with($this->equalTo('#resource'), $this->equalTo("*3\r\n$3\r\nDEL\r\n$3\r\nkey\r\n$4\r\ndata\r\n"))
            ->will($this->returnValue(5)); // only writes 5 bytes

        // So tries again with the remaining 27 bytes
        $mock->expects($this->at(2))
            ->method('write')
            ->with($this->equalTo('#resource'), $this->equalTo("3\r\nDEL\r\n$3\r\nkey\r\n$4\r\ndata\r\n"))
            ->will($this->returnValue(27));

        // Returns an integer response
        $mock->expects($this->at(3))
            ->method('gets')
            ->with($this->equalTo('#resource'))
            ->will($this->returnValue("+OK\r\n"));



        // Try a multi write with one inline response and one bulk response
        $mock->expects($this->at(4))
            ->method('write')
            ->with($this->equalTo('#resource'), $this->equalTo("*3\r\n$3\r\nSET\r\n$3\r\nkey\r\n$4\r\ndata\r\n"))
            ->will($this->returnValue(32));

        $mock->expects($this->at(5))
            ->method('write')
            ->with($this->equalTo('#resource'), $this->equalTo("*2\r\n$3\r\nGET\r\n$3\r\nkey\r\n"))
            ->will($this->returnValue(22));

        $mock->expects($this->at(6))
            ->method('gets')
            ->with($this->equalTo('#resource'))
            ->will($this->returnValue(":1\r\n"));

        $mock->expects($this->at(7))
            ->method('gets')
            ->with($this->equalTo('#resource'))
            ->will($this->returnValue("$4\r\n"));

        $mock->expects($this->at(8))
            ->method('read')
            ->with($this->equalTo('#resource'))
            ->will($this->returnValue("data"));


        // Close the connection
        $mock->expects($this->once())
            ->method('close')
            ->with($this->equalTo('#resource'));

        $obj = new Irediscent\Connection\SocketConnection();

        $obj->setSocketObject($mock);

        $this->assertFalse($obj->isConnected());

        $obj->connect();

        $this->assertTrue($obj->isConnected());

        $res = $obj->write(array(
            'DEL','key','data'
        ));

        $this->assertEquals(1, $res);

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

    public function testReConnect()
    {
        $mock = $this->getMock('Irediscent\Connection\SocketObject');

        $mock->expects($this->at(0))
            ->method('open')
            ->will($this->returnValue('#resource'));

        // Close the connection
        $mock->expects($this->at(1))
            ->method('close')
            ->with($this->equalTo('#resource'));


        $mock->expects($this->at(2))
            ->method('open')
            ->will($this->returnValue('#resource'));

        $obj = new Irediscent\Connection\SocketConnection();

        $obj->setSocketObject($mock);

        $this->assertFalse($obj->isConnected());

        $obj->connect();

        $this->assertTrue($obj->isConnected());

        $obj->reconnect();

        $this->assertTrue($obj->isConnected());
    }

    public function testAutoConnect()
    {
        $mock = $this->getMock('Irediscent\Connection\SocketObject');

        $mock->expects($this->at(0))
            ->method('open')
            ->will($this->returnValue('#resource'));

        $mock->expects($this->once())
            ->method('write')
            ->will($this->returnValue(14));

        $mock->expects($this->once())
            ->method('gets')
            ->will($this->returnValue(':1'));

        $obj = new Irediscent\Connection\SocketConnection();

        $obj->setSocketObject($mock);

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
    public function testRedisError()
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

    /**
     * @expectedException \Irediscent\Exception\ConnectionException
     */
    public function testConnectionErrorThrows()
    {
        $mock = $this->getMock('Irediscent\Connection\SocketObject');

        $mock->expects($this->once())
             ->method('open')
             ->will($this->returnValue(false));


        $obj = new Irediscent\Connection\SocketConnection();

        $obj->setSocketObject($mock);

        $obj->connect();
    }

    /**
     * @expectedException \Irediscent\Exception\TransmissionException
     */
    public function testTransmissionErrorThrows()
    {
        $mock = $this->getMock('Irediscent\Connection\SocketObject');

        $mock->expects($this->once())
            ->method('open')
            ->will($this->returnValue(true));


        $obj = new Irediscent\Connection\SocketConnection();

        $obj->setSocketObject($mock);

        $obj->connect();

        $obj->write(array(
            'DEL','key','data'
        ));
    }

    /**
     * @expectedException \Irediscent\Exception\TransmissionException
     */
    public function testTransmissionErrorReadThrows()
    {
        $mock = $this->getMock('Irediscent\Connection\SocketObject');

        $mock->expects($this->once())
            ->method('write')
            ->will($this->returnValue(14));

        $mock->expects($this->once())
            ->method('gets')
            ->will($this->returnValue("$4\n\r"));

        $mock->expects($this->once())
            ->method('read')
            ->will($this->returnValue(false));

        $obj = new Irediscent\Connection\SocketConnection();

        $obj->setSocketObject($mock);

        $obj->write(array(
            'data'
        ));
    }

    /**
     * @expectedException \Irediscent\Exception\UnknownResponseException
     */
    public function testUknownResponseErrorThrows()
    {
        $mock = $this->getMock('Irediscent\Connection\SocketObject');

        $mock->expects($this->once())
            ->method('write')
            ->will($this->returnValue(14));

        $mock->expects($this->once())
            ->method('gets')
            ->will($this->returnValue("%"));

        $obj = new Irediscent\Connection\SocketConnection();

        $obj->setSocketObject($mock);

        $obj->write(array(
            'data'
        ));
    }
}
