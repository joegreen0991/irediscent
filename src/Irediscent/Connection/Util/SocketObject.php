<?php namespace Irediscent\Connection\Util;

/**
 * @codeCoverageIgnore
 */
class SocketObject
{
    public function open($host, $port = null, &$errno = null, &$errstr = null, $timeout = null, $readwriteTimeout = null)
    {
        $resource = @fsockopen($host, $port, $errno, $errstr, $timeout);

        if(!$resource)
        {
            return false;
        }

        if (isset($readwriteTimeout)) {
            $readwriteTimeout = (float) $readwriteTimeout;
            $readwriteTimeout = $readwriteTimeout > 0 ? $readwriteTimeout : -1;
            $timeoutSeconds  = floor($readwriteTimeout);
            $timeoutUSeconds = ($readwriteTimeout - $timeoutSeconds) * 1000000;
            stream_set_timeout($resource, $timeoutSeconds, $timeoutUSeconds);
        }

        return $resource;
    }

    public function write($handle, $data)
    {
        return fwrite($handle, $data);
    }

    public function gets($handle, $length)
    {
        return fgets($handle, $length);
    }

    public function read($handle, $length)
    {
        return fread($handle, $length);
    }

    public function close($handle)
    {
        fclose($handle);
    }
}
