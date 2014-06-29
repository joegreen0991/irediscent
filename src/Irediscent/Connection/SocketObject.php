<?php namespace Irediscent\Connection;

/**
 * @codeCoverageIgnore
 */
class SocketObject
{
    public function open($host, $port, &$errno, &$errstr, $timeout)
    {
        return @fsockopen($host, $port, $errno, $errstr, $timeout);
    }

    public function write($handle, $data, $length = null)
    {
        return fwrite($handle, $data, $length);
    }

    public function read($handle, $length)
    {
        return fread($handle, $length);
    }

    public function gets($handle, $length = null)
    {
        return fgets($handle, $length);
    }

    public function close($handle)
    {
        fclose($handle);
    }
}