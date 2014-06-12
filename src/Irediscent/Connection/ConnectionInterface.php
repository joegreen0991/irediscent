<?php namespace Irediscent\Connection;

interface ConnectionInterface{

    public function connect();

    public function disconnect();

    public function write($data);

    public function multiWrite($data);
}
