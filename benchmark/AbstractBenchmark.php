<?php namespace benchmark\Irediscent;

abstract class AbstractBenchmark
{
    protected $r;

    function __construct()
    {
        $this->r = $this->getConnection();
        $this->r->flushdb();
    }

    abstract protected function getConnection($conn = null);

    public function run()
    {
        $this->runTest('lPushStringTest');
        $this->runTest('lPopStringTest');
        $this->runTest('setIntTest');
        $this->runTest('getIntTest');
    }

    protected function runTest($method)
    {
        $callback = $this->$method();

        $length = 10;

        $start = microtime(true);

        $count = 0;

        while(microtime(true) <  $start + $length)
        {
            $count++;
            $callback();
        }

        $ops = $count / $length;

        echo "$ops $method operations per second\n";
    }

    function lPushStringTest()
    {
        return function()
        {
            $this->r->lpush('benchmark:lpush', 'string');
        };
    }

    function lPopStringTest()
    {
        return function()
        {
            $this->r->lpop('benchmark:lpush');
        };
    }

    function setIntTest()
    {
        return function()
        {
            $this->r->set('benchmark:set', 1);
        };
    }

    function getIntTest()
    {
        return function()
        {
            $this->r->get('benchmark:set');
        };
    }
}
