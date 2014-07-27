<?php namespace Irediscent\Connection\Serializer;

interface SerializerInterface {

    public function serialize($data);

    public function read($data);
}
