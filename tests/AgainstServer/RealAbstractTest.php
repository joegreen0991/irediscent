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
        $this->assertEquals(true, $this->r->set('foo', 'bar'));
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

        $this->assertCount(10, $responses);
        $this->assertEquals(1, $this->r->del('X'));
    }

    function testItExecutesALuaScript() {

        $this->r->eval("redis.call('set',KEYS[1],'hello')", 1, 'evaltest');

        $this->assertEquals('hello', $this->r->eval("return redis.call('get',KEYS[1])", 1, 'evaltest'));
    }

    /**
     * @expectedException Irediscent\Exception\RedisException
     * @expectedExceptionMessage ERR unknown command 'UNKNOWNCOMMAND'
     */
    function testItThrowsRedisServerException()
    {
        $output = $this->r->unknownCommand();

        echo $output;
    }

    /**
     * @expectedException Irediscent\Exception\ConnectionException
     */
    function testItThrowsConnectionException() {

        $r = $this->getConnection('x.x.x.x:0');

        $r->connect();
    }

    function testItSetsAndReadsLargeData()
    {
        $score = 5000;

        $data = '{"Data1":"1","Data2":"2","Data3":"3"}';

        while($score--)
        {
            $this->r->zadd('testzrange', $score, $data);
        }

        $this->r->zrangebyscore('testzrange', 0, 'inf', 'WITHSCORES');

        $this->r->eval("return redis.call('zrangebyscore',KEYS[1],0,'inf')", 1, 'testzrange');
    }
}
