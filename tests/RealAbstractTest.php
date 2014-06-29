<?php namespace tests\Irediscent;

abstract class RealAbstractTest extends \PHPUnit_Framework_TestCase
{

    protected $r;

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
