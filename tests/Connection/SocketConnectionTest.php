<?php namespace tests\Irediscent;

use Irediscent;

class SocketConnectionTest extends \PHPUnit_Framework_TestCase
{

    public function testItCorrectlySendsCommandsToTheSerializer()
    {

        $mock = $this->getMock('Irediscent\Connection\Util\SocketObject');

        $mock->expects($this->once())
             ->method('open')
             ->will($this->returnValue('#resource'));

        $mock->expects($this->any())
            ->method('write')
            ->will($this->returnValue(1));

        $mock->expects($this->any())
            ->method('gets')
            ->will($this->returnValue(1));

        // Close the connection
        $mock->expects($this->once())
            ->method('close')
            ->with($this->equalTo('#resource'));

        $mockSerializer = $this->getMock('Irediscent\Connection\Serializer\SerializerInterface');

        $mockSerializer->expects($this->at(0))
            ->method('serialize')
            ->with(array(
                'DEL','key','data'
            ));

        $mockSerializer->expects($this->at(1))
            ->method('read')
            ->will($this->returnValue(true));

        $mockSerializer->expects($this->at(2))
            ->method('serialize')
            ->with(array('SET','key','data'));

        $mockSerializer->expects($this->at(3))
            ->method('serialize')
            ->with(array('GET','key'));

        $mockSerializer->expects($this->at(4))
            ->method('read')
            ->will($this->returnValue(
                1
            ));
        $mockSerializer->expects($this->at(5))
            ->method('read')
            ->will($this->returnValue(
                'data'
            ));

        $obj = new Irediscent\Connection\SocketConnection(null, $mockSerializer);

        $obj->setSocketObject($mock);

        $this->assertFalse($obj->isConnected());

        $obj->connect();

        $this->assertTrue($obj->isConnected());

        $res = $obj->write(array(
            'DEL','key','data'
        ));

        $this->assertEquals(true, $res);

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
        $mock = $this->getMock('Irediscent\Connection\Util\SocketObject');

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

        $mockSerializer = $this->getMock('Irediscent\Connection\Serializer\SerializerInterface');

        $obj = new Irediscent\Connection\SocketConnection(null, $mockSerializer);

        $obj->setSocketObject($mock);

        $this->assertFalse($obj->isConnected());

        $obj->connect();

        $this->assertTrue($obj->isConnected());

        $obj->reconnect();

        $this->assertTrue($obj->isConnected());
    }

    public function testItAutoConnectsWhenFirstCommandIsPerformed()
    {
        $mock = $this->getMock('Irediscent\Connection\Util\SocketObject');

        $mock->expects($this->at(0))
            ->method('open')
            ->will($this->returnValue('#resource'));

        $mock->expects($this->any())
            ->method('gets')
            ->will($this->returnValue(1));

        $mockSerializer = $this->getMock('Irediscent\Connection\Serializer\SerializerInterface');

        $obj = new Irediscent\Connection\SocketConnection(null, $mockSerializer);

        $obj->setSocketObject($mock);

        $this->assertFalse($obj->isConnected());

        $obj->write(array(
            'data'
        ));

        $this->assertTrue($obj->isConnected());
    }

    /**
     * @expectedException \Irediscent\Exception\ConnectionException
     */
    public function testItThrowsConnectionExceptionWhenServerConnectionOpenFails()
    {
        $mock = $this->getMock('Irediscent\Connection\Util\SocketObject');

        $mock->expects($this->once())
             ->method('open')
             ->will($this->returnValue(false));


        $mockSerializer = $this->getMock('Irediscent\Connection\Serializer\SerializerInterface');

        $obj = new Irediscent\Connection\SocketConnection(null, $mockSerializer);

        $obj->setSocketObject($mock);

        $obj->connect();
    }

    /**
     * @expectedException \Irediscent\Exception\TransmissionException
     */
    public function testItThrowsTransmissionErrorWhenNoDataIsReturnedFromWrite()
    {
        $mock = $this->getMock('Irediscent\Connection\Util\SocketObject');

        $mock->expects($this->once())
            ->method('open')
            ->will($this->returnValue(true));


        $mockSerializer = $this->getMock('Irediscent\Connection\Serializer\SerializerInterface');

        $obj = new Irediscent\Connection\SocketConnection(null, $mockSerializer);

        $obj->setSocketObject($mock);

        $obj->connect();

        $obj->write(array(
            'DEL','key','data'
        ));
    }

    /**
     * @expectedException \Irediscent\Exception\TransmissionException
     */
    public function testItThrowsTransmissionExceptionWhenNoDataIsReturnedFromRead()
    {
        $mock = $this->getMock('Irediscent\Connection\Util\SocketObject');

        $mock->expects($this->once())
            ->method('write')
            ->will($this->returnValue(14));

        $mock->expects($this->at(1))
            ->method('gets')
            ->will($this->returnValue(false));

        $mockSerializer = $this->getMock('Irediscent\Connection\Serializer\SerializerInterface');

        $mockSerializer->expects($this->at(0))
            ->method('serialize')
            ->with(array(
                'data'
            ))
            ->will($this->returnValue("data"));

        $obj = new Irediscent\Connection\SocketConnection(null, $mockSerializer);

        $obj->setSocketObject($mock);

        $obj->write(array(
            'data'
        ));
    }
}
