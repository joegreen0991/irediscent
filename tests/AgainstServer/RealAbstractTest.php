<?php namespace tests\Irediscent;

abstract class RealAbstractTest extends \PHPUnit_Framework_TestCase
{

    protected $r;

    function setUp()
    {
        $this->r = $this->getConnection();
    }

    abstract protected function getConnection($conn = null);

    function testItSetsAndGetsData()
    {
        $this->assertEquals('OK', $this->r->set('foo', 'bar'));
        $this->assertEquals($this->r->get('foo'), 'bar');

        $this->r->disconnect();
    }

    function testItHsetsAndGetsMultipleData()
    {
        $this->r->hmset('test', 'one', 1, 'two', 2);

        $this->assertEquals(array('one', 1, 'two', 2), $this->r->hgetall('test'));
    }

    function testItChecksKeyExists()
    {
        $this->assertEquals($this->r->exists('foo'), 1);
        $this->assertEquals($this->r->exists('bar'), 0);
    }

    function testItDeletesAKey() {
        $this->assertEquals($this->r->del('foo'), 1);
        $this->assertNull($this->r->get('foo'));
    }

    function testItCallsFluentPipelineIncr() {
        // Test the fluent interface
        $responses = $this->r->pipeline()
            ->set('X',1)
            ->incr('X')
            ->incr('X')
            ->incr('X')
            ->incr('X')
            ->uncork();
        $this->assertEquals(count($responses), 5);
        $this->assertEquals($this->r->get('X'), 5);
        $this->assertEquals($this->r->del('X'), 1);
    }

    function testItCallsProceduralPipelinedIncr() {
        // Test a less fluent interface
        $pipeline = $this->r->pipeline();
        for ($i = 0; $i < 10; $i++) {
            $pipeline->incr('X');
        }
        $responses = $pipeline->uncork();

        $this->assertEquals(count($responses), 10);
        $this->assertEquals($this->r->del('X'), 1);
    }

    /**
     * @expectedException Irediscent\Exception\RedisException
     * @expectedMessage unknown command 'UNKNOWNCOMMAND'

    function testItThrowsRedisServerException() {

        $this->r->unknownCommand();

    }*/

    /**
     * @expectedException Irediscent\Exception\ConnectionException
     * @expectedMessage unknown command 'UNKNOWNCOMMAND'
     */
    function testItThrowsConnectionException() {

        $r = $this->getConnection('x.x.x.x:0');

        $r->connect();
    }
}
