<?php namespace Irediscent\Connection\Util;

/**
 * @codeCoverageIgnore
 */
class SocketObject
{
    public function open($host, $port = null, &$errno = null, &$errstr = null, $timeout = null)
    {
        return @fsockopen($host, $port, $errno, $errstr, $timeout ?: ini_get("default_socket_timeout"));
    }

    public function write($handle, $data)
    {
        return fwrite($handle, $data);
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