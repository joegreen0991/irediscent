<?php namespace tests\Irediscent;

use Irediscent;

class StaticProviderTest extends \PHPUnit_Framework_TestCase
{

    public function testObject()
    {
        $return = array(
            'host' => '1.0.0.1',
            'port' => 25,
        );

        $slaves = array(
            array(
                'host' => '1.0.0.3',
                'port' => 26,
            ),
            '1.0.0.4'
        );

        $returnSlavesExp = array(
            array(
                'host' => '1.0.0.3',
                'port' => 26,
            ),
            array(
                'host' => '1.0.0.4',
                'port' => 6379,
            ),
        );

        $obj = new Irediscent\DsnProvider\StaticProvider($return, $slaves);

        $got = $obj->getMasterDsn();

        $this->assertEquals($return, $got);

        $got = $obj->getSlavesDsn();

        $this->assertEquals($returnSlavesExp, $got);
    }

    public function testObjectMasterEmpty()
    {
        $return = array(
            ''
        );

        $obj = new Irediscent\DsnProvider\StaticProvider($return);

        $got = $obj->getMasterDsn();

        $this->assertEquals(array(
            'host' => 'localhost',
            'port' => 6379,
        ), $got);
    }

    public function testGetSlaves()
    {
        $return = array(
            array(
                'name',
                '1.0.0.1:25',
                'ip',
                '1.0.0.1',
                'port',
                '25'
            ),
            array(
                'name',
                '1.0.0.2:26',
                'ip',
                '1.0.0.2',
                'port',
                '26'
            ),
        );

        $mock1 = $this->getMock('Irediscent\Connection\ConnectionInterface');

        $mock1->expects($this->once())
            ->method('connect');

        $mock1->expects($this->at(1))
            ->method('write')
            ->with($this->equalTo("SENTINEL slaves mymaster"))
            ->will($this->returnValue($return));

        $mock1->expects($this->at(2))
            ->method('write')
            ->with($this->equalTo("QUIT"));

        $sentinels = array(
            $mock1
        );

        $obj = new Irediscent\DsnProvider\SentinelProvider($sentinels, 'mymaster');

        $got = $obj->getSlavesDsn();

        $this->assertEquals(array(
            array(
                'host' => '1.0.0.1',
                'port' => 25
            ),
            array(
                'host' => '1.0.0.2',
                'port' => 26
            )
        ), $got);
    }

    public function testObjectFailsCorrectly()
    {
        $mock1 = $this->getMock('Irediscent\Connection\ConnectionInterface');
        $mock2 = $this->getMock('Irediscent\Connection\ConnectionInterface');
        $mock3 = $this->getMock('Irediscent\Connection\ConnectionInterface');

        $mock1->expects($this->at(0))
            ->method('connect')
            ->will($this->throwException(new Irediscent\Exception\ConnectionException));

        $mock1->expects($this->never())
            ->method('write');

        $mock2->expects($this->at(0))
            ->method('connect');

        $mock2->expects($this->at(1))
            ->method('write')
            ->with($this->equalTo("SENTINEL get-master-addr-by-name mymaster"));

        $mock2->expects($this->at(2))
            ->method('write')
            ->with($this->equalTo("QUIT"));

        $mock3->expects($this->never())
            ->method('write');

        $sentinels = array(
            $mock1,
            $mock2,
            $mock3
        );

        $obj = new Irediscent\DsnProvider\SentinelProvider($sentinels, 'mymaster');

        $obj->getMasterDsn();

    }

    /**
     * @expectedException Irediscent\Exception\NoSentinelsException
     */
    public function testObjectThrowsExceptionCorrectly()
    {
        $mock1 = $this->getMock('Irediscent\Connection\ConnectionInterface');
        $mock2 = $this->getMock('Irediscent\Connection\ConnectionInterface');

        $mock1->expects($this->at(0))
            ->method('connect')
            ->will($this->throwException(new Irediscent\Exception\ConnectionException));

        $mock2->expects($this->at(0))
            ->method('connect')
            ->will($this->throwException(new Irediscent\Exception\ConnectionException));

        $sentinels = array(
            $mock1,
            $mock2,
        );

        $obj = new Irediscent\DsnProvider\SentinelProvider($sentinels, 'mymaster');

        $obj->getMasterDsn();

    }

}
