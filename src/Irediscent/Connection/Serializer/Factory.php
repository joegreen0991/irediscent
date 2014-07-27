<?php namespace Irediscent\Connection\Serializer;

class Factory {
    /**
     * @return Irediscent\Connection\Serializer\IRedis | Irediscent\Connection\Serializer\PurePhp
     */
    public static function make()
    {
        if(function_exists('phpiredis_reader_create'))
        {
            return new IRedis();
        }
        else{
            return new PurePhp();
        }
    }
}
