<?php namespace tests\Irediscent;

abstract class RealAbstractTest extends \PHPUnit_Framework_TestCase
{

    protected $r;


    function testSet()
    {
        $this->assertEquals('OK', $this->r->set('foo', 'bar'));
        $this->assertEquals($this->r->get('foo'), 'bar');

        $this->r->disconnect();
    }

    function testHset()
    {
        $this->r->hmset('test', 'one', 1, 'two', 2);

        $this->assertEquals(array('one', 1, 'two', 2), $this->r->hgetall('test'));
    }

    function testExists()
    {
        $this->assertEquals($this->r->exists('foo'), 1);
        $this->assertEquals($this->r->exists('bar'), 0);
    }

    function testDel() {
        $this->assertEquals($this->r->del('foo'), 1);
        $this->assertNull($this->r->get('foo'));
    }

    function testFluentIncr() {
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

    function testProceduralIncr() {
        // Test a less fluent interface
        $pipeline = $this->r->pipeline();
        for ($i = 0; $i < 10; $i++) {
            $pipeline->incr('X');
        }
        $responses = $pipeline->uncork();

        $this->assertEquals(count($responses), 10);
        $this->assertEquals($this->r->del('X'), 1);
    }

}
