<?php namespace Irediscent\Connection;

use Irediscent\DsnProvider\DsnProviderInterface;
use Irediscent\DsnProvider\StaticProvider;

abstract class ConnectionAbstract implements ConnectionInterface {

    /**
     * @var \Irediscent\DsnProvider\DsnProviderInterface
     */
    protected $dsn;


    public function __construct($dsn = null)
    {
        $this->dsn = $dsn instanceof DsnProviderInterface ? $dsn : new StaticProvider($dsn);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function write($data)
    {
        $this->writeCommand($data);

        return $this->readResponse();
    }

    /**
     * @param $data
     * @return array
     */
    public function multiWrite($data)
    {
        /* Open a Redis connection and execute the queued commands */
        foreach ($data as $rawCommand)
        {
            $this->writeCommand($rawCommand);
        }

        // Read in the results from the pipelined commands
        $responses = array();
        for ($i = 0; $i < count($data); $i++)
        {
            $responses[] = $this->readResponse();
        }

        return $responses;
    }

    abstract protected function writeCommand($data);

    abstract protected function readResponse();
}
