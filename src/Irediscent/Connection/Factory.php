<?php namespace Irediscent\Connection;

class Factory {
    /**
     * @return Irediscent\Connection\IredisSocketConnection | Irediscent\Connection\SocketConnection
     */
    public static function make($dsn = null, $timeout = null)
    {
        if(function_exists('phpiredis_reader_create'))
        {
            return new IredisSocketConnection($dsn, $timeout);
        }
        else{
            return new SocketConnection($dsn, $timeout);
        }
    }
}
